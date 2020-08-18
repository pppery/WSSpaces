<?php

namespace PDP;

use MediaWiki\Auth\AuthManager;
use MediaWiki\Session\Session;

abstract class PDPHooks {
    const SECONDS_HOUR = 3600;

    public static function onSecuritySensitiveOperationStatus( string &$status, string $operation, Session $session, int $timeSinceAuth ): bool {
        if ( $operation === "pega-change-permissions" && $timeSinceAuth > self::SECONDS_HOUR ) {
            $status = AuthManager::SEC_REAUTH;
        }

        return true;
    }
}