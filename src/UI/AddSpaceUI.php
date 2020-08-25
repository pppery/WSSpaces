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
     * Renders the UI.
     *
     * @return void
     */
    public function render() {
        $this->getOutput()->addWikiMsg( 'pdp-add-space-intro' );
        $this->showForm();
    }

    /**
     * Returns the header text shown in the UI.
     *
     * @return string
     */
    public function getHeader(): string {
        return wfMessage( 'pdp-add-space-header' )->plain();
    }

    /**
     * Shows the "Create new space" form.
     */
    private function showForm() {
        $form = new AddSpaceForm(
            $this->getOutput(),
            new AddSpaceSubmitCallback( $this ),
            new AddSpaceValidationCallback()
        );

        $form->show();
    }
}