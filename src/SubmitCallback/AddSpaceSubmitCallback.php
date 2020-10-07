<?php

namespace WSS\SubmitCallback;

use WSS\NamespaceRepository;
use WSS\Space;
use WSS\UI\WSSUI;
use WSS\UI\SpacesUI;

/**
 * Class AddSpaceSubmitCallback
 *
 * @package WSS\SubmitCallback
 */
class AddSpaceSubmitCallback implements SubmitCallback {
    /**
     * @var WSSUI
     */
    private $ui;

    /**
     * SubmitCallback constructor.
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
        if ( !isset( $form_data['displayname'] ) || empty( $form_data['displayname'] ) ) {
            return "wss-invalid-input";
        }

        if ( !isset( $form_data['description'] ) || empty( $form_data['description'] ) ) {
            return "wss-invalid-input";
        }

        if ( !isset( $form_data['namespace'] ) || empty( $form_data['namespace'] ) ) {
            return "wss-invalid-input";
        }

        $display_name = $form_data['displayname'];
        $description  = $form_data['description'];
        $namespace    = $form_data['namespace'];

        $space = Space::newFromValues( $namespace, $display_name, $description, \RequestContext::getMain()->getUser() );

        $namespace_repository = new NamespaceRepository();
        $namespace_repository->addSpace( $space );

        $this->ui->setAllowCallback();

        \RequestContext::getMain()->getOutput()->redirect(
            \Title::newFromText( "ManageSpace", NS_SPECIAL )->getFullUrlForRedirect(
                [ 'wss_callback' => 'created' ]
            )
        );

        return true;
    }
}