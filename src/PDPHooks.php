<?php

namespace PDP;

use MediaWiki\Auth\AuthManager;
use MediaWiki\Session\Session;
use PDP\UI\PDPUI;

abstract class PDPHooks {
    const TIMEOUT = 12000;

    public static function onSecuritySensitiveOperationStatus( string &$status, string $operation, Session $session, int $timeSinceAuth ): bool {
        if ( $operation === "pega-change-permissions" && $timeSinceAuth > self::TIMEOUT ) {
            $status = AuthManager::SEC_REAUTH;
        }

        return true;
    }

    public static function onSkinBuildSidebar( \Skin $skin, &$bar ) {
        if (!PDPUI::isQueued()) {
            return true;
        }

        $bar[wfMessage('pdp-sidebar-header')->plain()][] = [
            'text' => wfMessage( 'pdp-special-title' ),
            'href' => \Title::newFromText( "Permissions", NS_SPECIAL )->getFullUrlForRedirect(),
            'id'   => 'pdp-permissions-special',
            'active' => ''
        ];

        return true;
    }
}