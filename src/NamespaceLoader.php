<?php

namespace WSS;

use Hooks;
use PDO;
use PDOException;

/**
 * Class NamespaceLoader
 *
 * @package WSS
 */
class NamespaceLoader {
    /**
     * Dynamically loads the namespaces defined for WSSpaces. For strange reasons, the normal DB_REPLICA is
     * not ready at the point this class is loaded when loaded through the api.php endpoint. Therefore, we
     * HAVE to make a regular PHP database query for it to work.
     */
    public static function loadNamespaces() {
        global $wgDBtype, $wgDBserver, $wgDBname, $wgDBuser, $wgDBpassword, $wgDBprefix, $wgSQLiteDataDir;

        if ( $wgDBtype === "sqlite" ) {
            $dsn = "sqlite:$wgSQLiteDataDir/$wgDBname.sqlite";
        } else {
            // TODO: Make this work with Postgres schemas.
            $dsn = "$wgDBtype:host=$wgDBserver;dbname=$wgDBprefix$wgDBname;charset=utf8mb4";
        }

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ];

        try {
            $pdo = new PDO( $dsn, $wgDBuser, $wgDBpassword, $options );
        } catch ( PDOException $e ) {
            throw new PDOException( $e->getMessage(), (int)$e->getCode() );
        }

        $statement = $pdo->query(
            "SELECT `namespace_id`, `namespace_key` FROM `wss_namespaces` WHERE `archived` = false"
        );

        while ( $row = $statement->fetch() ) {
            Hooks::run( "WSSpacesBeforeInitializeSpace", [$row['namespace_id'], $row['namespace_key']] );

            // Add the namespace to $wgExtraNamespaces
            $GLOBALS['wgExtraNamespaces'][$row['namespace_id']] = $row['namespace_key'];
            // Add the namespace to $wgContentNamespaces
            $GLOBALS['wgContentNamespaces'][] = $row['namespace_id'];
            // Add the namespace to the default search index
            $GLOBALS['wgNamespacesToBeSearchedDefault'][$row['namespace_id']] = true;
            // Enable Semantic MediaWiki for the namespace
            $GLOBALS["smwgNamespacesWithSemanticLinks"][$row['namespace_id']] = true;
        }
    }

}