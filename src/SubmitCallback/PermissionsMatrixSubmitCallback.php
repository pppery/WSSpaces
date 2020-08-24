<?php

namespace PDP\SubmitCallback;

use PDP\PermissionsMatrix;
use PDP\UI\PDPUI;
use Wikimedia\Rdbms\Database;

class PermissionsMatrixSubmitCallback implements SubmitCallback {
    /**
     * @var int
     */
    private $namespace_constant;

    /**
     * @var PDPUI
     */
    private $ui;

    /**
     * SubmitCallback constructor.
     * @param PDPUI $ui
     * @param int $namespace_constant
     */
    public function __construct( PDPUI $ui, int $namespace_constant ) {
        $this->ui = $ui;
        $this->namespace_constant = $namespace_constant;
    }

    /**
     * Called upon submitting a form.
     *
     * @param array $form_data The data submitted via the form.
     * @return string|bool
     */
    public function onSubmit( array $form_data ) {
        $permissions_matrix = PermissionsMatrix::newFromFormData( $form_data, "checkmatrix" );
        $permissions_matrix->setNamespaceConstant( $this->namespace_constant );

        $database = wfGetDB( DB_MASTER );

        $this->storePermissionsMatrix( $permissions_matrix, $database );

        $this->ui->addModule("ext.pdp.SpecialPermissionsSuccess");

        // We want the form to still appear.
        return false;
    }

    /**
     * Stores the given PermissionsMatrix into the given Database.
     *
     * @param PermissionsMatrix $permissions_matrix
     * @param Database $database
     */
    private function storePermissionsMatrix( PermissionsMatrix $permissions_matrix, Database $database ) {
        $namespace_constant = $permissions_matrix->getNamespaceConstant();

        $this->deleteRecordsWithNamespaceConstant( $namespace_constant, $database );

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
    private function deleteRecordsWithNamespaceConstant( int $namespace_constant, Database $database ) {
        $database->delete(
            'pdp_permissions',
            [ 'namespace' => $namespace_constant ]
        );
    }
}