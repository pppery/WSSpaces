<?php

namespace WSS\UI;

use WSS\Form\EditSpaceForm;
use WSS\Space;
use WSS\SubmitCallback\EditSpaceSubmitCallback;
use WSS\Validation\AddSpaceValidationCallback;

/**
 * Class ManageSpaceBaseUI
 * @package WSS\UI
 */
class ManageSpaceFormUI extends ManageSpaceUI {
    /**
     * @inheritDoc
     */
    public function getHeader(): string {
        $namespace = $this->getParameter();
        $space = Space::newFromName( $namespace );
        $display_name = $space ? $space->getDisplayName() : $namespace;

        return wfMessage( "wss-manage-space-form-header", $display_name )->plain();
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
            'label-message' => 'wss-archive-space',
            'id' => 'wss-archive-space',
            'flags' => 'destructive',
            'framed' => false
        ]);

        $form->show();
    }
}