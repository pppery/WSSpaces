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
    public function getHeader(): string {
        $space = Space::newFromConstant( $this->getParameter() );
        return wfMessage("wss-manage-space-header", $space->getName() );
    }

    /**
     * @inheritDoc
     * @throws \ConfigException
     */
    function render() {
        $parameter = $this->getParameter();
        $space = Space::newFromConstant( $parameter );

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