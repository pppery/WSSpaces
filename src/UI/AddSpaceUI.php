<?php

namespace PDP\UI;

use PDP\Form\AddSpaceForm;
use PDP\SubmitCallback\AddSpaceSubmitCallback;
use PDP\Validation\AddSpaceValidationCallback;

/**
 * Class NamespacesUI
 *
 * @package PDP\UI
 */
class AddSpaceUI extends SpacesUI {
    /**
     * @inheritDoc
     */
    public function render() {
        $this->getOutput()->addWikiMsg( 'pdp-add-space-intro' );

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