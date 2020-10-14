<?php

namespace WSS\SubmitCallback;

use WSS\NamespaceRepository;
use WSS\Space;
use WSS\UI\WSSUI;
use WSS\UI\SpacesUI;

class EditSpaceSubmitCallback implements SubmitCallback {
    /**
     * @var WSSUI
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

        $old_space = Space::newFromName( $form_data['namespacename'] );
        $new_space = Space::newFromName( $form_data['namespacename'] );

        $namespace_repository = new NamespaceRepository();

        if ( $archive === "archive" ) {
            $namespace_repository->archiveSpace( $old_space );

            $this->ui->setAllowCallback();
            \RequestContext::getMain()->getOutput()->redirect(
                \Title::newFromText( "ManageSpace", NS_SPECIAL )->getFullUrlForRedirect(
                    [ 'wss_callback' => 'archived' ]
                )
            );

            return true;
        }

        $new_space->setDescription( $form_data['description'] );
        $new_space->setDisplayName( $form_data['displayname'] );
        $new_space->setSpaceAdministrators( explode("\n", $form_data['administrators']) );

        try {
            $namespace_repository->updateSpace( $old_space, $new_space );
        } catch( \PermissionsError $e ) {
            return "wss-cannot-remove-self";
        }

        $this->ui->addModule("ext.wss.SpecialManageSpaceSuccess");

        return false;
    }
}