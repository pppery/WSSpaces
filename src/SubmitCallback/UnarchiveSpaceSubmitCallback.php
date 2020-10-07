<?php

namespace WSS\SubmitCallback;

use WSS\NamespaceRepository;
use WSS\PermissionsHandler;
use WSS\PermissionsMatrix;
use WSS\Space;
use WSS\UI\WSSUI;
use WSS\UI\SpacesUI;

class UnarchiveSpaceSubmitCallback implements SubmitCallback {
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
        $space = Space::newFromName( $form_data['namespacename'] );

        $namespace_repository = new NamespaceRepository();
        $namespace_repository->unarchiveSpace( $space );

        $this->ui->setAllowCallback();

        \RequestContext::getMain()->getOutput()->redirect(
            \Title::newFromText( "ManageSpace", NS_SPECIAL )->getFullUrlForRedirect(
                [ 'wss_callback' => 'unarchived' ]
            )
        );

        return true;
    }
}