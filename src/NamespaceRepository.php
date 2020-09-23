<?php

namespace PDP;

use Config;
use ConfigException;
use ExtensionRegistry;
use MediaWiki\MediaWikiServices;

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
     * @var array
     */
    private $valid_canonical_namespaces;

    /**
     * NamespaceRepository constructor.
     *
     * @throws ConfigException
     */
    public function __construct() {
        $this->canonical_namespaces         = [ NS_MAIN => 'Main' ] + $this->getConfig()->get( 'CanonicalNamespaceNames' );
        $this->extension_namespaces         = ExtensionRegistry::getInstance()->getAttribute( 'ExtensionNamespaces' );
        $this->valid_canonical_namespaces   = $this->getConfig()->get( 'PDPValidNamespaces' );
    }

    /**
     * Returns the next available namespace id.
     */
    public static function getNextAvailableNamespaceId(): int {
        $dbr = wfGetDB( DB_MASTER );
        $result = $dbr->select(
              'pdp_namespaces',
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
     * Returns an array of all valid core (and extension) namespaces. The key of the array returned is the namespace
     * constant and the value returned is the namespace name.
     *
     * @return array
     */
    public function getCoreNamespaces() {
        $canonical_namespaces = array_intersect( $this->getValidCanonicalNamespaces(), $this->getCanonicalNamespaces() );
        $extension_namespaces = $this->getExtensionNamespaces();

        return $canonical_namespaces + $extension_namespaces;
    }

    /**
     * Gets all applicable namespaces. When the first parameter is true,
     * the key will be the name of the namespace, and the value the constant, otherwise the key will be the namespace
     * constant and the value the namespace name.
     *
     * @param bool $flip
     * @return array
     */
    public function getNamespaces( $flip = false ): array {
        $canonical_namespaces = array_intersect( $this->getValidCanonicalNamespaces(), $this->getCanonicalNamespaces() );
        $extension_namespaces = $this->getExtensionNamespaces();
        $spaces               = $this->getSpaces();

        $namespaces = $canonical_namespaces + $extension_namespaces + $spaces;

        return $flip ? array_flip( $namespaces ) : $namespaces;
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
     * Returns the list of all (archived and unarchived) dynamic spaces defined by the PDP extension. When the first
     * parameter is true, the key will be the name of the namespace, and the value the
     * constant, otherwise the key will be the namespace constant and the value the namespace name.
     *
     * @param bool $flip
     * @return array
     */
    public function getAllSpaces( $flip = false ): array {
        $dbr = wfGetDB( DB_REPLICA );
        $result = $dbr->select(
            'pdp_namespaces',
            [
                'namespace_id',
                'namespace_name'
            ]
        );

        $buffer = [];
        foreach ( $result as $item ) {
            $buffer[$item->namespace_id] = $item->namespace_name;
        }

        return $flip ? array_flip( $buffer ) : $buffer;
    }

    /**
     * Returns the list of unarchived dynamic spaces defined by the PDP extension. When the first parameter is true,
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
     * Returns the list of archived dynamic spaces defined by the PDP extension. When
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
     * Returns the list of valid canonical namespaces as defined by $wgPDPValidNamespaces.
     *
     * @return array
     */
    public function getValidCanonicalNamespaces(): array {
        return $this->valid_canonical_namespaces;
    }

    /**
     * Adds the given Space to the database.
     *
     * @param Space $space
     */
    public function addSpace( Space $space ) {
        if ( $space->exists() ) {
            throw new \InvalidArgumentException( "Cannot add existing space to database, use NamespaceRepository::updateSpace() instead." );
        }

        $database = wfGetDB( DB_MASTER );
        $database->insert(
        'pdp_namespaces',  [
            'namespace_id' => self::getNextAvailableNamespaceId(),
            'namespace_name' => $space->getName(),
            'display_name' => $space->getDisplayName(),
            'description' => $space->getDescription(),
            'archived' => $space->isArchived(),
            'creator_id' => $space->getOwner()->getId(),
            'created_on' => time()
        ] );

        // Create a new space from the name, go get the latest details from the database.
        $space = $space::newFromName( $space->getName() );

        $space->setSpaceAdministrators( [ $space->getOwner()->getName() ] );
        $this->updateSpaceAdministrators( $database, $space );
    }

    /**
     * Updates an existing space in the database.
     *
     * @param Space $space
     * @param bool $force True to force the creation of the space and skip the permission check
     * @throws \PermissionsError
     */
    public function updateSpace( Space $space, bool $force = false ) {
        if ( !$space->exists() ) {
            throw new \InvalidArgumentException( "Cannot update non-existing space in database, use NamespaceRepository::addSpace() instead." );
        }

        // Last minute check to see if the user actually does have enough permissions to edit this space.
        if ( !$space->canEdit() && !$force ) {
            throw new \PermissionsError( "Not enough permissions to edit this space." );
        }

        $database = wfGetDB( DB_MASTER );
        $database->update('pdp_namespaces', [
            'display_name' => $space->getDisplayName(),
            'description' => $space->getDescription(),
            'creator_id' => $space->getOwner()->getId(),
            'archived' => $space->isArchived()
        ], [
            'namespace_id' => $space->getId()
        ] );

        $this->updateSpaceAdministrators( $database, $space );
    }

    /**
     * Helper function to archive a namespace.
     *
     * @param Space $space
     * @throws \PermissionsError
     */
    public function archiveSpace( Space $space ) {
        $space->setArchived();
        $this->updateSpace( $space );
    }

    /**
     * Helper function to unarchive a namespace.
     *
     * @param Space $space
     * @throws \PermissionsError
     */
    public function unarchiveSpace( Space $space ) {
        $space->setArchived( false );
        $this->updateSpace( $space );
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
     * @param \Database $database
     * @param Space $space
     */
    private function updateSpaceAdministrators( \Database $database, Space $space ) {
        $namespace_id = $space->getId();

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
                        return $user->isAnon() ? 0 : $user->getId();
                    }, $space->getSpaceAdministrators()
                ),
                function ( int $id ): bool {
                    return $id !== 0;
                }
            )
        );

        $rows = array_values( $rows );

        $database->delete('pdp_namespace_admins', [
            "namespace_id" => $namespace_id
        ] );

        $database->insert( 'pdp_namespace_admins', $rows );
    }

    /**
     * Gets all spaces available to the current logged in user, based on whether they are archived
     * or not.
     *
     * @param bool $archived True to only get archived spaces, false otherwise
     * @return array
     */
    private function getSpacesOnArchived(bool $archived ): array {
        $dbr = wfGetDB( DB_REPLICA );
        $result = $dbr->select(
            'pdp_namespaces',
            [
                'namespace_id',
                'namespace_name'
            ],
            [
                'archived' => $archived
            ]
        );

        $buffer = [];
        foreach ( $result as $item ) {
            $buffer[$item->namespace_id] = $item->namespace_name;
        }

        return $buffer;
    }
}