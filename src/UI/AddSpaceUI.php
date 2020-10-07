<?php

namespace WSS\UI;

use WSS\Form\AddSpaceForm;
use WSS\SubmitCallback\AddSpaceSubmitCallback;
use WSS\Validation\AddSpaceValidationCallback;

/**
 * Class NamespacesUI
 *
 * @package WSS\UI
 */
class AddSpaceUI extends SpacesUI {
    /**
     * @inheritDoc
     */
    public function render() {
        $this->getOutput()->addWikiMsg( 'wss-add-space-intro' );

        $form = new AddSpaceForm(
            $this->getOutput(),
            new AddSpaceSubmitCallback( $this ),
            new AddSpaceValidationCallback()
        );

        $form->show();
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier(): string {
        return "add-space";
    }
}