<?php

namespace WSS\SubmitCallback;

use WSS\PermissionsHandler;
use WSS\PermissionsMatrix;
use WSS\UI\WSSUI;
use Wikimedia\Rdbms\Database;

class PermissionsMatrixSubmitCallback implements SubmitCallback {
    /**
     * @var int
     */
    private $namespace_constant;

    /**
     * @var WSSUI
     */
    private $ui;

    /**
     * SubmitCallback constructor.
     * @param WSSUI $ui
     * @param int $namespace_constant
     */
    public function __construct( WSSUI $ui, int $namespace_constant ) {
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
        $old_matrix = PermissionsMatrix::newFromNamespaceConstant( $this->namespace_constant );

        $new_matrix = PermissionsMatrix::newFromFormData( $form_data, "checkmatrix" );
        $new_matrix->setNamespaceConstant( $this->namespace_constant );

        try {
            PermissionsHandler::storePermissionsMatrix($old_matrix, $new_matrix, wfGetDB(DB_MASTER));
        } catch (\ConfigException $e) {
            return "wss-generic-inline-error";
        } catch (\MWException $e) {
            return "wss-generic-inline-error";
        }

        $this->ui->addModule("ext.wss.SpecialPermissionsSuccess");

        // We want the form to still appear.
        return false;
    }
}