<?php

namespace PDP\SubmitCallback;

use PDP\NamespaceRepository;
use PDP\Space;
use PDP\UI\PDPUI;
use PDP\UI\SpacesUI;

class EditSpaceSubmitCallback implements SubmitCallback {
    /**
     * @var PDPUI
     */
    private $ui;

    /**
     * EditSpaceSubmitCallback constructor.
     *
     * @param SpacesUI $ui
     */
    public function __construct( SpacesUI $ui ) {
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
        $archive = \RequestContext::getMain()->getRequest()->getVal( 'archive' );

        $space = Space::newFromName( $form_data['namespacename'] );
        $namespace_repository = new NamespaceRepository();

        if ( $archive === "archive" ) {
            $namespace_repository->archiveSpace( $space );

            $this->ui->setAllowCallback();

            \RequestContext::getMain()->getOutput()->redirect(
                \Title::newFromText( "ManageSpace", NS_SPECIAL )->getFullUrlForRedirect(
                    [ 'pdp_callback' => 'archived' ]
                )
            );

            return true;
        }

        $space->setDescription( $form_data['description'] );
        $space->setDisplayName( $form_data['displayname'] );
        $space->setSpaceAdministrators( explode("\n", $form_data['administrators']) );

        $namespace_repository->updateSpace( $space );

        $this->ui->addModule("ext.pdp.SpecialManageSpaceSuccess");

        return false;
    }
}