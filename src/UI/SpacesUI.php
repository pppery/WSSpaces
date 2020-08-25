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
            'Active spaces' => 'Special:ManageSpace',
            'Archived spaces' => 'Special:ArchivedSpaces',
            'Create space' => 'Special:AddSpace'
        ];
    }
}