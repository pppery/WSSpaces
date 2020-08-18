<?php


namespace PDP\UI;

use PDP\Form\PermissionsMatrixForm;
use PDP\Handler\PermissionsMatrixHandler;
use PDP\Validation\PermissionsMatrixValidationCallback;

/**
 * Class PermissionsUI
 *
 * @package PDP\UI
 */
class PermissionsUI extends PDPUI {
    /**
     * @inheritDoc
     */
    public function execute() {
        $this->showPermissionsMatrixForm();
    }

    /**
     * Shows the PermissionsMatrix form.
     */
    private function showPermissionsMatrixForm() {
        $form = new PermissionsMatrixForm(
            $this->getOutput(),
            new PermissionsMatrixHandler(),
            new PermissionsMatrixValidationCallback()
        );

        $form->show();
    }
}