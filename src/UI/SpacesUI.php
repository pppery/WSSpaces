<?php


namespace PDP\UI;

/**
 * Class SpacesUI
 *
 * @package PDP\UI
 */
abstract class SpacesUI extends PDPUI {
    /**
     * @inheritDoc
     */
    public function getHeaderPrefix(): string {
        return "\u{1f4d0}";
    }

    /**
     * @inheritDoc
     */
    public function getNavigationPrefix(): string {
        return wfMessage( 'pdp-space-topnav-prefix' )->plain();
    }

    /**
     * @inheritDoc
     */
    public function getNavigationItems(): array {
        return [
            wfMessage( 'pdp-add-space-header' )->plain() => 'Special:AddSpace',
            wfMessage( 'pdp-manage-space-header' )->plain() => 'Special:ManageSpace',
            wfMessage( 'pdp-archived-spaces-header' )->plain() => 'Special:ArchivedSpaces'
        ];
    }

    /**
     * @inheritDoc
     */
    public function getModules(): array {
        return array_merge( [ 'ext.pdp.Spaces' ], $this->getConditionalModules() );
    }

    /**
     * Sets a flag to allow for a pdp_callback query parameter.
     */
    public function setAllowCallback() {
        $this->getOutput()->getRequest()->getSession()->set( "callback-allowed", true );
    }

    /**
     * Returns an array of modules that are conditional.
     *
     * @return array
     */
    public function getConditionalModules(): array {
        $request = $this->getOutput()->getRequest();

        $session = $request->getSession();
        $callback_allowed = $session->exists( "callback-allowed" );

        if ( !$callback_allowed ) {
            return [];
        }

        $callback = $request->getVal( 'pdp_callback' );

        if ( $callback === null ) {
            return [];
        }

        $session->remove( "callback-allowed" );

        switch( $callback ) {
            case "created":
                return [ "ext.pdp.AddSpaceSuccess" ];
            case "archived":
                return [ "ext.pdp.ArchiveSpaceSuccess" ];
            case "unarchived":
                return [ "ext.pdp.UnarchiveSpaceSuccess" ];
            default:
                return [];
        }
    }
}