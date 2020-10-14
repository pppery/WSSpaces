<?php

namespace WSS;

use Wikimedia\Rdbms\Database;
use WSS\Log\UpdatePermissionsLog;

class PermissionsHandler {
    /**
     * Stores the given PermissionsMatrix into the given Database.
     *
     * @param PermissionsMatrix $old_matrix
     * @param PermissionsMatrix $new_matrix
     * @param Database $database
     * @throws \ConfigException
     * @throws \MWException
     */
    public static function storePermissionsMatrix( PermissionsMatrix $old_matrix, PermissionsMatrix $new_matrix, Database $database ) {
        $old_namespace_constant = $old_matrix->getNamespaceConstant();
        $new_namespace_constant = $new_matrix->getNamespaceConstant();

        $log = new UpdatePermissionsLog( $old_matrix, $new_matrix );
        $log->insert();

        $database->delete(
            'wss_permissions',
            [ 'namespace' => $old_namespace_constant ]
        );

        if ( count( $new_matrix ) === 0 ) {
            return;
        }

        foreach ($new_matrix as $permission ) {
            $group = $permission['group'];
            $right = $permission['right'];

            $database->insert( 'wss_permissions',
                [
                    'namespace'  => $new_namespace_constant,
                    'user_group' => $group,
                    'user_right' => $right
                ]
            );
        }

        $log->publish();
    }
}