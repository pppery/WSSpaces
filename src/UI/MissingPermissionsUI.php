<?php

namespace WSS\UI;

use MediaWiki\Linker\LinkRenderer;
use OutputPage;

class MissingPermissionsUI extends WSSUI {
    public function __construct(OutputPage $page, LinkRenderer $link_renderer) {
        parent::__construct($page, $link_renderer);
    }

    /**
     * Renders the UI.
     *
     * @return void
     */
    function render() {
        $this->getOutput()->addWikiMsg( 'wss-missing-permissions-description' );
    }

    /**
     * @inheritDoc
     */
    function getIdentifier(): string {
        return 'missing-permissions';
    }

    /**
     * @inheritDoc
     */
    public function getHeaderPrefix(): string {
        return "\u{1f512}";
    }

    /**
     * @inheritDoc
     */
    function getNavigationPrefix(): string {
        return wfMessage('wss-missing-permissions-topnav')->plain();
    }

    /**
     * @inheritDoc
     */
    function getNavigationItems(): array {
        return [
            wfMessage( 'wss-add-space-header' )->plain() => 'Special:AddSpace',
            wfMessage( 'wss-active-spaces-header' )->plain() => 'Special:ActiveSpaces',
            wfMessage( 'wss-archived-spaces-header' )->plain() => 'Special:ArchivedSpaces'
        ];
    }
}