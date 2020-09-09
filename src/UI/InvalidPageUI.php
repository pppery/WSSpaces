<?php

namespace PDP\UI;

class InvalidPageUI extends PDPUI {
    /**
     * Renders the UI.
     *
     * @return void
     */
    function render() {
        $this->getOutput()->addWikiMsg( 'pdp-invalid-page-description' );
    }

    /**
     * @inheritDoc
     */
    function getIdentifier(): string {
        return 'invalid-page';
    }

    /**
     * @inheritDoc
     */
    public function getHeaderPrefix(): string {
        return "\u{274C}";
    }

    /**
     * @inheritDoc
     */
    function getNavigationPrefix(): string {
        return wfMessage('pdp-invalidpage-topnav')->plain();
    }

    /**
     * @inheritDoc
     */
    function getNavigationItems(): array {
        return [
            wfMessage( 'pdp-special-permissions-title' )->plain() => 'Special:Permissions',
            wfMessage( 'pdp-add-space-header' )->plain() => 'Special:AddSpace',
            wfMessage( 'pdp-manage-space-header' )->plain() => 'Special:ManageSpace',
            wfMessage( 'pdp-archived-spaces-header' )->plain() => 'Special:ArchivedSpaces'
        ];
    }
}