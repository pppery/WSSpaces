<?php

namespace WSS;

use MediaWiki\Auth\AuthManager;
use MediaWiki\Session\Session;
use MWException;
use WSS\UI\WSSUI;

abstract class WSSHooks {
    const TIMEOUT = 12000;

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
            "ws-create-namespaces",
            "ws-change-permissions"
        ];

        if ( $session->getLoggedOutTimestamp() > 0 ) {
            $status = AuthManager::SEC_FAIL;
        } else if ( in_array( $operation, $security_sensitive_operations ) && $timeSinceAuth > self::TIMEOUT ) {
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
     * @throws \ConfigException
     */
    public static function onSkinBuildSidebar( \Skin $skin, &$bar ) {
        if ( !WSSUI::isQueued() ) {
            return true;
        }

        if ( in_array( 'wss-edit-core-namespaces', \RequestContext::getMain()->getUser()->getRights() ) ) {
            $bar[wfMessage('wss-sidebar-header')->plain()][] = [
                'text' => wfMessage( 'wss-special-permissions-title' ),
                'href' => \Title::newFromText( "Permissions", NS_SPECIAL )->getFullUrlForRedirect(),
                'id'   => 'wss-permissions-special',
                'active' => ''
            ];
        }

        $bar[wfMessage('wss-sidebar-header')->plain()][] = [
            'text' => wfMessage( 'wss-add-space-header' ),
            'href' => \Title::newFromText( "AddSpace", NS_SPECIAL )->getFullUrlForRedirect(),
            'id'   => 'wss-add-space-special',
            'active' => ''
        ];

        $bar[wfMessage('wss-sidebar-header')->plain()][] = [
            'text' => wfMessage( 'wss-manage-space-header' ),
            'href' => \Title::newFromText( "ManageSpace", NS_SPECIAL )->getFullUrlForRedirect(),
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
     * @throws MWException
     */
    public static function onLoadExtensionSchemaUpdates( \DatabaseUpdater $updater ) {
        $directory = $GLOBALS['wgExtensionDirectory'] . '/WSSpaces/sql';
        $type = $updater->getDB()->getType();

        $wss_permissions_table = sprintf( "%s/%s/wss_permissions_table.sql", $directory, $type );
        $wss_namespaces_table = sprintf( "%s/%s/wss_namespaces_table.sql", $directory, $type );
        $wss_namespace_admins_table = sprintf( "%s/%s/wss_namespace_admins_table.sql", $directory, $type );

        if (
            !file_exists( $wss_permissions_table ) ||
            !file_exists( $wss_namespaces_table )  ||
            !file_exists( $wss_namespace_admins_table )
        ) {
            throw new MWException( "WSS does not support database type `$type`." );
        }

        $updater->addExtensionTable( 'wss_permissions', $wss_permissions_table );
        $updater->addExtensionTable( 'wss_namespaces', $wss_namespaces_table );
        $updater->addExtensionTable( 'wss_namespace_admins', $wss_namespace_admins_table );
    }

    /**
     * Occurs before anything is initialized in MediaWiki::performRequest().
     *
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforeInitialize
     *
     * @param \Title $title
     * @param $unused
     * @param \OutputPage $output
     * @param \User $user
     * @param \WebRequest $request
     * @param \MediaWiki $mediaWiki
     * @throws \ConfigException
     */
    public static function onBeforeInitialize(
        \Title &$title,
        $unused,
        \OutputPage $output,
        \User $user,
        \WebRequest $request,
        \MediaWiki $mediaWiki
    ) {
        $handler = new LockdownHandler();
        $handler->setLockdownConstraints();
    }

    /**
     * For extensions adding their own namespaces or altering the defaults.
     *
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/CanonicalNamespaces
     *
     * @param array $namespaces
     * @throws \ConfigException
     */
    public static function onCanonicalNamespaces( array &$namespaces ) {
        $namespace_repository = new NamespaceRepository();
        $spaces = $namespace_repository->getSpaces();

        $namespaces = $namespaces + $spaces;
    }
}