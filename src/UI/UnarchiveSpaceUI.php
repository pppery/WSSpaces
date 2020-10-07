<?php


namespace WSS\UI;

use WSS\Form\UnarchiveSpaceForm;
use WSS\Space;
use WSS\SubmitCallback\UnarchiveSpaceSubmitCallback;
use WSS\Validation\UnarchiveSpaceValidationCallback;

/**
 * Class UnarchiveSpaceUI
 *
 * @package WSS\UI
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