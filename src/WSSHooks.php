<?php

namespace WSS;

use ConfigException;
use Exception;
use MediaWiki\Auth\AuthManager;
use MediaWiki\MediaWikiServices;
use MediaWiki\Session\Session;
use MWException;
use WSS\UI\WSSUI;

abstract class WSSHooks {
    const TIMEOUT = 12000;

    /**
     * Hook WSSpaces into the parser.
     *
     * @param Parser $parser
     */
    public static function onParserFirstCallInit( \Parser $parser ) {
        $parser->setFunctionHook( 'spaceadmins', [ self::class, 'renderSpaceAdmins' ] );
    }

    /**
     * Render the output of {{#spaceadmins: namespace}}.
     */
    public static function renderSpaceAdmins( \Parser $parser, $namespace = '' ) : string {
        // Validate namespace input.
        if ($namespace === '') {
            return "No namespace provided.";
        }
        if (!ctype_digit($namespace)) {
            return "Namespace can only be (positive) numbers.";
        }

        // Get all admin ids for a namespace. If no admins are found, return the error message.
        $admin_ids = NamespaceRepository::getNamespaceAdmins($namespace);
        if (empty($admin_ids)) {
            return "No admins found for namespace: $namespace!";
        }

        // Turn the list of admin ids into User objects
        $admins = array_map( [ \User::class, "newFromId" ], $admin_ids );
        $admins = array_filter($admins, function ($user):bool { return ($user instanceof \User); });
        if (empty($admins)) {
            return "No admins are valid MediaWiki Users!";
        }

        // Add all admin names to a comma separated string.
        $admins = array_map(fn(\User $user)=>$user->getName(), $admins);

        // Return the admin list.
        return implode(",", $admins);
    }

    /**
     * Affect the return value from AuthManager::securitySensitiveOperationStatus().
     *
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/SecuritySensitiveOperationStatus
     *
     * @param string $status
     * @param string $operation
     * @param Session $session
     * @param int $timeSinceAuth
     * @return bool
     */
    public static function onSecuritySensitiveOperationStatus( string &$status, string $operation, Session $session, int $timeSinceAuth ): bool {
        $security_sensitive_operations = [
            "ws-manage-namespaces",
            "ws-create-namespaces"
        ];

        if ( $session->getLoggedOutTimestamp() > 0 ) {
            $status = AuthManager::SEC_FAIL;
        } else if ( in_array( $operation, $security_sensitive_operations, true ) && $timeSinceAuth > self::TIMEOUT ) {
            $status = AuthManager::SEC_REAUTH;
        } else {
            $status = AuthManager::SEC_OK;
        }

        return true;
    }

    /**
     * At the end of Skin::buildSidebar().
     *
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/SkinBuildSidebar
     *
     * @param \Skin $skin
     * @param $bar
     * @return bool
     * @throws ConfigException
     */
    public static function onSkinBuildSidebar( \Skin $skin, &$bar ): bool {
        if ( !WSSUI::isQueued() ) {
            return true;
        }

        $bar[wfMessage('wss-sidebar-header')->plain()][] = [
            'text' => wfMessage( 'wss-add-space-header' ),
            'href' => \Title::newFromText( "AddSpace", NS_SPECIAL )->getFullUrlForRedirect(),
            'id'   => 'wss-add-space-special',
            'active' => ''
        ];

        $bar[wfMessage('wss-sidebar-header')->plain()][] = [
            'text' => wfMessage( 'wss-active-spaces-header' ),
            'href' => \Title::newFromText( "ActiveSpaces", NS_SPECIAL )->getFullUrlForRedirect(),
            'id'   => 'wss-manage-space-special',
            'active' => ''
        ];

        $bar[wfMessage('wss-sidebar-header')->plain()][] = [
            'text' => wfMessage( 'wss-archived-spaces-header' ),
            'href' => \Title::newFromText( "ArchivedSpaces", NS_SPECIAL )->getFullUrlForRedirect(),
            'id'   => 'wss-archived-spaces-special',
            'active' => ''
        ];

        WSSUI::setSidebar( $bar );

        return true;
    }

    /**
     * Fired when MediaWiki is updated to allow extensions to register updates for the database schema.
     *
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/LoadExtensionSchemaUpdates
     *
     * @param \DatabaseUpdater $updater
     * @return bool
     * @throws MWException
     */
    public static function onLoadExtensionSchemaUpdates( \DatabaseUpdater $updater ): bool {
        $directory = $GLOBALS['wgExtensionDirectory'] . '/WSSpaces/sql';
        $type = $updater->getDB()->getType();

        // Associative array where the keys are the name of the table and the value the name of the associated SQL file
        $sql_files = [
            "wss_namespaces" => "wss_namespaces_table.sql",
            "wss_namespace_admins" => "wss_namespace_admins_table.sql"
        ];

        foreach ( $sql_files as $table => $sql_file ) {
            $path = sprintf( "%s/%s/%s", $directory, $type, $sql_file );

            if ( !file_exists( $path ) ) {
                throw new MWException( "WSSpaces does not support database type `$type`.` Please use `mysql`, `postgres` or `sqlite`." );
            }

            $updater->addExtensionTable( $table, $path );
        }

        return true;
    }

    /**
     * For extensions adding their own namespaces or altering the defaults.
     *
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/CanonicalNamespaces
     *
     * @param array $namespaces
     * @return bool
     *
     * @throws ConfigException
     * @throws Exception
     *
     * Running the hook was commented out because creating the Space objects from the db was causing slowdown.
     * TODO: Fix slowdown issue.
     */
    public static function onCanonicalNamespaces( array &$namespaces ): bool {
        $namespace_repository = new NamespaceRepository();
        $spaces = $namespace_repository->getSpaces();

        foreach ( $spaces as $constant => $name ) {
//            MediaWikiServices::getInstance()->getHookContainer()->run(
//                "WSSpacesBeforeInitializeSpace",
//                [Space::newFromConstant($constant)]
//            );
            $namespaces[$constant] = $name;
        }

        return true;
    }

    /**
     * Called when generating the extensions credits, use this to change the tables headers.
     *
     * @param $extension_types
     * @return bool
     */
    public static function onExtensionTypes( array &$extension_types ): bool {
        $extension_types[ 'contentmanagement' ] = wfMessage( "version-contentmanagement" )->parse();
        return true;
    }
}