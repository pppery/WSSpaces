<?php

namespace PDP\SubmitCallback;

use PDP\NamespaceRepository;
use PDP\Space;
use PDP\UI\PDPUI;
use PDP\UI\SpacesUI;

/**
 * Class AddSpaceSubmitCallback
 *
 * @package PDP\SubmitCallback
 */
class AddSpaceSubmitCallback implements SubmitCallback {
    /**
     * @var PDPUI
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
            return "pdp-invalid-input";
        }

        if ( !isset( $form_data['description'] ) || empty( $form_data['description'] ) ) {
            return "pdp-invalid-input";
        }

        if ( !isset( $form_data['namespace'] ) || empty( $form_data['namespace'] ) ) {
            return "pdp-invalid-input";
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
                [ 'pdp_callback' => 'created' ]
            )
        );

        return true;
    }
}