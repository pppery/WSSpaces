<?php

namespace PDP\SubmitCallback;

use PDP\PermissionsHandler;
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

        PermissionsHandler::storePermissionsMatrix( $permissions_matrix, $database );

        $this->ui->addModule("ext.pdp.SpecialPermissionsSuccess");

        // We want the form to still appear.
        return false;
    }
}