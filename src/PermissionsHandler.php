<?php

namespace PDP;

use Wikimedia\Rdbms\Database;

class PermissionsHandler {
    /**
     * Stores the given PermissionsMatrix into the given Database.
     *
     * @param PermissionsMatrix $permissions_matrix
     * @param Database $database
     */
    public static function storePermissionsMatrix( PermissionsMatrix $permissions_matrix, Database $database ) {
        $namespace_constant = $permissions_matrix->getNamespaceConstant();

        self::deleteRecordsWithNamespaceConstant( $namespace_constant, $database );

        if ( count( $permissions_matrix ) === 0 ) {
            return;
        }

        foreach ( $permissions_matrix as $permission ) {
            $group = $permission['group'];
            $right = $permission['right'];

            $database->insert( 'pdp_permissions',
                [
                    'namespace'  => $namespace_constant,
                    'user_group' => $group,
                    'user_right' => $right
                ]
            );
        }
    }

    /**
     * Deletes all records associated with the given namespace constant from the given Database.
     *
     * @param int $namespace_constant
     * @param Database $database
     */
    private static function deleteRecordsWithNamespaceConstant( int $namespace_constant, Database $database ) {
        $database->delete(
            'pdp_permissions',
            [ 'namespace' => $namespace_constant ]
        );
    }
}