<?php

namespace WSS\UI;

use MediaWiki\MediaWikiServices;
use RequestContext;
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
        $space = Space::newFromConstant( (int)$this->getParameter() );
        return wfMessage("wss-manage-space-header", $space->getKey() );
    }

    /**
     * @inheritDoc
     * @throws \ConfigException
     */
    function render() {
        $namespace_constant = (int)$this->getParameter();
        $space = Space::newFromConstant( $namespace_constant );

        $form = new EditSpaceForm(
            $space,
            $this->getOutput(),
            new EditSpaceSubmitCallback( $this ),
            new AddSpaceValidationCallback()
        );

        if ( Space::canArchive() ) {
            $form->getForm()->addButton( [
                'name' => 'archive',
                'value' => 'archive',
                'label-message' => 'wss-archive-space',
                'id' => 'wss-archive-space',
                'flags' => 'destructive',
                'framed' => false
            ] );
        }

        $form->show();
    }
}