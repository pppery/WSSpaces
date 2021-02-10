<?php

namespace WSS\UI;

use MediaWiki\MediaWikiServices;

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
}