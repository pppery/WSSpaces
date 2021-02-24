<?php

namespace WSS;

use Config;
use ConfigException;
use ExtensionRegistry;
use MediaWiki\MediaWikiServices;
use MWException;
use PermissionsError;
use User;
use WSS\Log\AddSpaceLog;
use WSS\Log\ArchiveSpaceLog;
use WSS\Log\UnarchiveSpaceLog;
use WSS\Log\UpdateSpaceLog;

class NamespaceRepository {
    // Lowest allowed ID for a space.
    const MIN_SPACE_ID = 50000;

    /**
     * @var array
     */
    private $canonical_namespaces;

    /**
     * @var array
     */
    private $extension_namespaces;

    /**
     * NamespaceRepository constructor.
     *
     * @throws ConfigException
     */
    public function __construct() {
        $this->canonical_namespaces         = [ NS_MAIN => 'Main' ] + $this->getConfig()->get( 'CanonicalNamespaceNames' );
        $this->extension_namespaces         = ExtensionRegistry::getInstance()->getAttribute( 'ExtensionNamespaces' );
    }

    /**
     * Returns the next available namespace id.
     */
    public static function getNextAvailableNamespaceId(): int {
        $dbr = wfGetDB( DB_MASTER );
        $result = $dbr->select(
              'wss_namespaces',
            [ 'namespace_id' ],
            '',
            __METHOD__,
            [ 'ORDER BY' => 'namespace_id DESC' ]
        );

        if ( $result->numRows() === 0 ) {
            return self::MIN_SPACE_ID;
        }

        $greatest_id = $result->current()->namespace_id;

        // + 2 because we need to skip the talk page.
        return $greatest_id + 2;
    }

    /**
     * Gets all namespaces. When the first parameter is true, the key will be the name
     * of the namespace, and the value the constant, otherwise the key will be the namespace constant and
     * the value the namespace name.
     *
     * @param bool $flip
     * @return array
     */
    public function getAllNamespaces( $flip = false ): array {
        $canonical_namespaces = $this->getCanonicalNamespaces();
        $extension_namespaces = $this->getExtensionNamespaces();
        $spaces               = $this->getAllSpaces();

        $namespaces = $canonical_namespaces + $extension_namespaces + $spaces;

        return $flip ? array_flip( $namespaces ) : $namespaces;
    }

    /**
     * Returns the list of all (archived and unarchived) dynamic spaces defined by the WSS extension. When the first
     * parameter is true, the key will be the name of the namespace, and the value the
     * constant, otherwise the key will be the namespace constant and the value the namespace name.
     *
     * @param bool $flip
     * @return array
     */
    public function getAllSpaces( $flip = false ): array {
        $dbr = wfGetDB( DB_REPLICA );
        $result = $dbr->select(
            'wss_namespaces',
            [
                'namespace_id',
                'namespace_key'
            ]
        );

        $buffer = [];
        foreach ( $result as $item ) {
            $buffer[$item->namespace_id] = $item->namespace_key;
        }

        return $flip ? array_flip( $buffer ) : $buffer;
    }

    /**
     * Returns the list of unarchived dynamic spaces defined by the WSS extension. When the first parameter is true,
     * the key will be the name of the namespace, and the value the constant, otherwise
     * the key will be the namespace constant and the value the namespace name.
     *
     * @param bool $flip
     * @return array
     */
    public function getSpaces( $flip = false ): array {
        $result = $this->getSpacesOnArchived( false );
        return $flip ? array_flip( $result ) : $result;
    }

    /**
     * Returns the list of archived dynamic spaces defined by the WSS extension. When
     * the first parameter is true, the key will be the name of the namespace, and the value the constant,
     * otherwise the key will be the namespace constant and the value the namespace name.
     *
     * @param bool $flip
     * @return array
     */
    public function getArchivedSpaces( $flip = false ): array {
        $result = $this->getSpacesOnArchived( true );
        return $flip ? array_flip( $result ) : $result;
    }

    /**
     * Returns the list of canonical namespace names as a key-value pair. When the first parameter is true, the key
     * will be the name of the namespace, and the value the constant, otherwise the key will be the namespace
     * constant and the value the namespace name.
     *
     * @param bool $flip
     * @return array
     */
    public function getCanonicalNamespaces( $flip = false ): array {
        return $flip ? array_flip( $this->canonical_namespaces ) : $this->canonical_namespaces;
    }

    /**
     * Returns the list of namespace names defined by MediaWiki extensions. When the first parameter is true, the
     * key will be the name of the namespace, and the value the constant, otherwise the key will be the namespace
     * constant and the value the namespace name.
     *
     * @param bool $flip
     * @return array
     */
    public function getExtensionNamespaces( $flip = false ): array {
        return $flip ? array_flip( $this->extension_namespaces ) : $this->extension_namespaces;
    }

    /**
     * Adds the given Space to the database.
     *
     * @param Space $space
     * @return int The ID of the created namespace
     * @throws MWException
     * @throws ConfigException
     * @throws \Exception
     */
    public function addSpace( Space $space ): int {
        if ( $space->exists() ) {
            throw new \InvalidArgumentException( "Cannot add existing space to database, use NamespaceRepository::updateSpace() instead." );
        }

        // We publish the log first, since we
        $log = new AddSpaceLog( $space );
        $log->insert();

        $namespace_id = self::getNextAvailableNamespaceId();

        $database = wfGetDB( DB_MASTER );
        $database->insert(
        'wss_namespaces',  [
            'namespace_id' => $namespace_id,
            'namespace_name' => $space->getName(),
            'namespace_key' => $space->getKey(),
            'description' => $space->getDescription(),
            'archived' => $space->isArchived(),
            'creator_id' => $space->getOwner()->getId(),
            'created_on' => time()
        ] );

        // Create a new space from the name, go get the latest details from the database.
        $space = Space::newFromConstant( $namespace_id );

        $space->setSpaceAdministrators( [ $space->getOwner()->getName() ] );
        $this->updateSpaceAdministrators( $database, $space );

        $log->publish();

        \Hooks::run( "WSSpacesAfterCreateSpace", [ $space ] );

        return $namespace_id;
    }

    /**
     * Updates an existing space in the database.
     *
     * @param Space|false $old_space
     * @param Space $new_space
     * @param bool $force True to force the creation of the space and skip the permission check
     * @param bool $log Whether or not to log this update (true by default)
     *
     * @throws MWException
     * @throws PermissionsError
     */
    public function updateSpace( $old_space, Space $new_space, bool $force = false, bool $log = true ) {
        if ( $old_space === false || !$old_space->exists() ) {
            throw new \InvalidArgumentException( "Cannot update non-existing space in database, use NamespaceRepository::addSpace() instead." );
        }

        // Last minute check to see if the user actually does have enough permissions to edit this space.
        if ( !$new_space->canEdit() && !$force ) {
            throw new PermissionsError( "Not enough permissions to edit this space." );
        }

        if ( $log ) {
            $log = new UpdateSpaceLog( $old_space, $new_space );
            $log->insert();
        }

        $database = wfGetDB( DB_MASTER );
        $database->update('wss_namespaces', [
            'namespace_key' => $new_space->getKey(),
            'namespace_name' => $new_space->getName(),
            'description' => $new_space->getDescription(),
            'creator_id' => $new_space->getOwner()->getId(),
            'archived' => $new_space->isArchived()
        ], [
            'namespace_id' => $old_space->getId()
        ] );

        $this->updateSpaceAdministrators( $database, $new_space );

        if ( $log ) {
            $log->publish();
        }
    }

    /**
     * Helper function to archive a namespace.
     *
     * @param Space $space
     * @throws MWException
     * @throws PermissionsError
     */
    public function archiveSpace( Space $space ) {
        $log = new ArchiveSpaceLog( $space );
        $log->insert();

        $new_space = clone $space;
        $new_space->setArchived();

        // Because of the way "updateSpace" works, we need a clone of the original
        // space
        $this->updateSpace( $space, $new_space, false, false );

        $log->publish();
    }

    /**
     * Helper function to unarchive a namespace.
     *
     * @param Space $space
     * @throws MWException
     * @throws PermissionsError
     */
    public function unarchiveSpace( Space $space ) {
        $log = new UnarchiveSpaceLog( $space );
        $log->insert();

        $new_space = clone $space;
        $new_space->setArchived( false );

        $this->updateSpace( $space, $new_space, false, false );

        $log->publish();
    }

    /**
     * Returns the main MediaWiki configuration.
     *
     * @return Config
     */
    private function getConfig(): Config {
        return MediaWikiServices::getInstance()->getMainConfig();
    }

    /**
     * Updates the space administrators for the given space. Should only be called by self::updateSpace().
     *
     * @param \Database|\DBConnRef $database
     * @param Space $space
     */
    private function updateSpaceAdministrators( $database, Space $space ) {
        $namespace_id = $space->getId();
        $rows = $this->createRowsFromSpaceAdministrators( $space->getSpaceAdministrators(), $namespace_id );

        $database->delete('wss_namespace_admins', [
            "namespace_id" => $namespace_id
        ] );

        $database->insert( 'wss_namespace_admins', $rows );
    }

    /**
     * Gets all spaces available to the current logged in user, based on whether they are archived
     * or not.
     *
     * @param bool $archived True to only get archived spaces, false otherwise
     * @return array
     */
    private function getSpacesOnArchived( bool $archived ): array {
        $dbr = wfGetDB( DB_REPLICA );
        $result = $dbr->select(
            'wss_namespaces',
            [
                'namespace_id',
                'namespace_key'
            ],
            [
                'archived' => $archived
            ]
        );

        $buffer = [];
        foreach ( $result as $item ) {
            $buffer[$item->namespace_id] = $item->namespace_key;
        }

        return $buffer;
    }

    private function createRowsFromSpaceAdministrators( array $administrators, $namespace_id ) {
        // FIXME: This is dumb and unreadable
        $rows = array_map(
            function ( int $admin_id ) use ( $namespace_id ): array {
                return [
                    "namespace_id" => $namespace_id,
                    "admin_user_id" => $admin_id
                ];
            },
            array_filter(
                array_map(
                    function ( string $admin ): int {
                        // This function returns the ID of the given administrator, or 0 if they dont exist.
                        $user = \User::newFromName( $admin );

                        if ( !$user instanceof User ) {
                            return 0;
                        }

                        return $user->isAnon() ? 0 : $user->getId();
                    }, $administrators
                ),
                function ( int $id ): bool {
                    return $id !== 0;
                }
            )
        );

        return array_values( $rows );
    }
}