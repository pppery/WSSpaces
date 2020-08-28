<?php

namespace PDP;

use MediaWiki\Auth\AuthManager;
use MediaWiki\Session\Session;
use MWException;
use PDP\UI\PDPUI;

abstract class PDPHooks {
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
            "pega-manage-namespaces",
            "pega-create-namespaces",
            "pega-change-permissions"
        ];

        if ( in_array( $operation, $security_sensitive_operations ) && $timeSinceAuth > self::TIMEOUT ) {
            $status = AuthManager::SEC_REAUTH;
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
     */
    public static function onSkinBuildSidebar( \Skin $skin, &$bar ) {
        if ( !PDPUI::isQueued() ) {
            return true;
        }

        $bar[wfMessage('pdp-sidebar-header')->plain()][] = [
            'text' => wfMessage( 'pdp-special-permissions-title' ),
            'href' => \Title::newFromText( "Permissions", NS_SPECIAL )->getFullUrlForRedirect(),
            'id'   => 'pdp-permissions-special',
            'active' => ''
        ];

        $bar[wfMessage('pdp-sidebar-header')->plain()][] = [
            'text' => wfMessage( 'pdp-add-space-header' ),
            'href' => \Title::newFromText( "AddSpace", NS_SPECIAL )->getFullUrlForRedirect(),
            'id'   => 'pdp-add-space-special',
            'active' => ''
        ];

        $bar[wfMessage('pdp-sidebar-header')->plain()][] = [
            'text' => wfMessage( 'pdp-manage-space-header' ),
            'href' => \Title::newFromText( "ManageSpace", NS_SPECIAL )->getFullUrlForRedirect(),
            'id'   => 'pdp-manage-space-special',
            'active' => ''
        ];

        $bar[wfMessage('pdp-sidebar-header')->plain()][] = [
            'text' => wfMessage( 'pdp-archived-spaces-header' ),
            'href' => \Title::newFromText( "ArchivedSpaces", NS_SPECIAL )->getFullUrlForRedirect(),
            'id'   => 'pdp-archived-spaces-special',
            'active' => ''
        ];

        PDPUI::setSidebar( $bar );

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
        $directory = $GLOBALS['wgExtensionDirectory'] . '/PegaDepartmentalPermissions/sql';
        $type = $updater->getDB()->getType();

        $pdp_permissions_table = sprintf( "%s/%s/pdp_permissions_table.sql", $directory, $type );
        if ( !file_exists( $pdp_permissions_table ) ) {
            throw new MWException( "PDP does not support database type `$type`." );
        }

        $pdp_namespaces_table = sprintf( "%s/%s/pdp_namespaces_table.sql", $directory, $type );
        if ( !file_exists( $pdp_namespaces_table ) ) {
            throw new MWException( "PDP does not support database type `$type`." );
        }

        $updater->addExtensionTable( 'pdp_permissions', $pdp_permissions_table );
        $updater->addExtensionTable( 'pdp_namespaces', $pdp_namespaces_table );
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