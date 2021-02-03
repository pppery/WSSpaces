<?php

namespace WSS\SubmitCallback;

use MediaWiki\MediaWikiServices;
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
     * @throws \MWException
     * @throws \PermissionsError
     */
    public function onSubmit( array $form_data ) {
        $archive = \RequestContext::getMain()->getRequest()->getVal( 'archive' );

        $old_space = Space::newFromConstant( $form_data['namespaceid'] );
        $new_space = Space::newFromConstant( $form_data['namespaceid'] );

        $namespace_repository = new NamespaceRepository();

        if ( $archive === "archive" && MediaWikiServices::getInstance()->getMainConfig()->get( "WSSpacesEnableSpaceArchiving" ) ) {
            $namespace_repository->archiveSpace( $old_space, $new_space );

            $this->ui->setAllowCallback();
            \RequestContext::getMain()->getOutput()->redirect(
                \Title::newFromText( "ActiveSpaces", NS_SPECIAL )->getFullUrlForRedirect(
                    [ 'wss_callback' => 'archived' ]
                )
            );

            return true;
        }

        $new_space->setKey( $form_data['namespace'] );
        $new_space->setDescription( $form_data['description'] );
        $new_space->setName( $form_data['namespace_name'] );
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