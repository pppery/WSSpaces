<?php


namespace PDP\UI;

use PDP\Form\UnarchiveSpaceForm;
use PDP\Space;
use PDP\SubmitCallback\UnarchiveSpaceSubmitCallback;
use PDP\Validation\UnarchiveSpaceValidationCallback;

/**
 * Class UnarchiveSpaceUI
 *
 * @package PDP\UI
 */
class UnarchiveSpaceUI extends SpacesUI {
    /**
     * @inheritDoc
     */
    function render() {
        $namespace = $this->getParameter();
        $space = Space::newFromName( $namespace );

        $form = new UnarchiveSpaceForm(
            $space,
            $this->getOutput(),
            new UnarchiveSpaceSubmitCallback( $this ),
            new UnarchiveSpaceValidationCallback()
        );

        $form->show();
    }

    /**
     * @inheritDoc
     */
    function getIdentifier(): string {
        return 'unarchive-space';
    }
}