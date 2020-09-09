<?php

namespace PDP\UI;

use PDP\Form\EditSpaceForm;
use PDP\Space;
use PDP\SubmitCallback\EditSpaceSubmitCallback;
use PDP\Validation\AddSpaceValidationCallback;

/**
 * Class ManageSpaceBaseUI
 * @package PDP\UI
 */
class ManageSpaceFormUI extends ManageSpaceUI {
    /**
     * @inheritDoc
     */
    public function getHeader(): string {
        $namespace = $this->getParameter();
        $space = Space::newFromName( $namespace );
        $display_name = $space ? $space->getDisplayName() : $namespace;

        return wfMessage( "pdp-manage-space-form-header", $display_name )->plain();
    }

    /**
     * @inheritDoc
     */
    function render() {
        $namespace = $this->getParameter();
        $space = Space::newFromName( $namespace );

        $form = new EditSpaceForm(
            $space,
            $this->getOutput(),
            new EditSpaceSubmitCallback( $this ),
            new AddSpaceValidationCallback()
        );

        $form->getForm()->addButton([
            'name' => 'archive',
            'value' => 'archive',
            'label-message' => 'pdp-archive-space',
            'id' => 'pdp-archive-space',
            'flags' => 'destructive',
            'framed' => false
        ]);

        $form->show();
    }
}