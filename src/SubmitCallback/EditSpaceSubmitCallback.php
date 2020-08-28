<?php

namespace PDP\SubmitCallback;

use PDP\NamespaceRepository;
use PDP\Space;
use PDP\UI\PDPUI;

class EditSpaceSubmitCallback implements SubmitCallback {
    /**
     * @var PDPUI
     */
    private $ui;

    /**
     * EditSpaceSubmitCallback constructor.
     *
     * @param PDPUI $ui
     */
    public function __construct( PDPUI $ui ) {
        $this->ui = $ui;
    }

    /**
     * Called upon submitting a form.
     *
     * @param array $form_data The data submitted via the form.
     * @return string|bool
     * @throws \ConfigException
     */
    public function onSubmit( array $form_data ) {
        $name = $form_data['namespacename'];
        $space = Space::newFromName( $name );

        $space->setDescription(
            $form_data['description']
        );

        $space->setDisplayName(
            $form_data['displayname']
        );

        $namespace_repository = new NamespaceRepository();
        $namespace_repository->updateSpace( $space );

        $this->ui->addModule("ext.pdp.SpecialManageSpaceSuccess");

        return false;
    }
}