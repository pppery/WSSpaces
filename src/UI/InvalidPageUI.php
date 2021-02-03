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

    /**
     * @inheritDoc
     */
    function getNavigationItems(): array {
        $menu = [
            wfMessage( 'wss-add-space-header' )->plain() => 'Special:AddSpace',
            wfMessage( 'wss-active-spaces-header' )->plain() => 'Special:ActiveSpaces'
        ];

        if ( MediaWikiServices::getInstance()->getMainConfig()->get( "WSSpacesEnableSpaceArchiving" ) ) {
            $menu[wfMessage( 'wss-archived-spaces-header' )->plain()] = 'Special:ArchivedSpaces';
        }

        return $menu;
    }
}