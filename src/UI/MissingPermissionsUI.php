<?php

namespace PDP\UI;

use MediaWiki\Linker\LinkRenderer;
use OutputPage;

class MissingPermissionsUI extends PDPUI {
    public function __construct(OutputPage $page, LinkRenderer $link_renderer) {
        parent::__construct($page, $link_renderer);
    }

    /**
     * Renders the UI.
     *
     * @return void
     */
    function render() {
        $this->getOutput()->addWikiMsg( 'pdp-missing-permissions-description' );
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
        return wfMessage('pdp-missing-permissions-topnav')->plain();
    }

    /**
     * @inheritDoc
     */
    function getNavigationItems(): array {
        return [
            wfMessage( 'pdp-add-space-header' )->plain() => 'Special:AddSpace',
            wfMessage( 'pdp-manage-space-header' )->plain() => 'Special:ManageSpace',
            wfMessage( 'pdp-archived-spaces-header' )->plain() => 'Special:ArchivedSpaces'
        ];
    }
}