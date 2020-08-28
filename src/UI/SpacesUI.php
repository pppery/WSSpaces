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
        return [ 'ext.pdp.Spaces' ];
    }
}