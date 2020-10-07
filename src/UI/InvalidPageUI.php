<?php

namespace WSS\UI;

class InvalidPageUI extends WSSUI {
    /**
     * Renders the UI.
     *
     * @return void
     */
    function render() {
        $this->getOutput()->addWikiMsg( 'wss-invalid-page-description' );
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
        return wfMessage('wss-invalidpage-topnav')->plain();
    }

    /**
     * @inheritDoc
     */
    function getNavigationItems(): array {
        return [
            wfMessage( 'wss-add-space-header' )->plain() => 'Special:AddSpace',
            wfMessage( 'wss-manage-space-header' )->plain() => 'Special:ManageSpace',
            wfMessage( 'wss-archived-spaces-header' )->plain() => 'Special:ArchivedSpaces'
        ];
    }
}