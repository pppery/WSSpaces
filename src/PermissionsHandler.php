<?php

namespace WSS;

use Wikimedia\Rdbms\Database;
use WSS\Log\UpdatePermissionsLog;

class PermissionsHandler {
    /**
     * Stores the given PermissionsMatrix into the given Database.
     *
     * @param PermissionsMatrix $permissions_matrix
     * @param Database $database
     * @throws \MWException
     * @throws \ConfigException
     */
    public static function storePermissionsMatrix( PermissionsMatrix $permissions_matrix, Database $database ) {
        $namespace_constant = $permissions_matrix->getNamespaceConstant();

        // TODO: Move retrieving old version of object to caller
        $old_matrix = PermissionsMatrix::newFromNamespaceConstant( $namespace_constant );

        $log = new UpdatePermissionsLog( $old_matrix, $permissions_matrix );
        $log->insert();

        $database->delete(
            'wss_permissions',
            [ 'namespace' => $namespace_constant ]
        );

        if ( count( $permissions_matrix ) === 0 ) {
            return;
        }

        foreach ( $permissions_matrix as $permission ) {
            $group = $permission['group'];
            $right = $permission['right'];

            $database->insert( 'wss_permissions',
                [
                    'namespace'  => $namespace_constant,
                    'user_group' => $group,
                    'user_right' => $right
                ]
            );
        }

        $log->publish();
    }
}