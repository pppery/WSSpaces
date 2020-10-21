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
     * @throws \ConfigException
     */
    function render() {
        $parameter = $this->getParameter();
        $space = Space::newFromConstant( $parameter );

        $form = new UnarchiveSpaceForm(
            $space,
            $this->getOutput(),
            new UnarchiveSpaceSubmitCallback( $this )
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