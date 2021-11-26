<?php

namespace WSS\UI;

use MediaWiki\Linker\LinkRenderer;
use OutputPage;

class MissingPermissionsUI extends WSSUI {
	/**
	 * MissingPermissionsUI constructor.
	 *
	 * @param OutputPage $page
	 * @param LinkRenderer $link_renderer
	 * @throws \MWException
	 */
	public function __construct( OutputPage $page, LinkRenderer $link_renderer ) {
		parent::__construct( $page, $link_renderer );
	}

	/**
	 * Renders the UI.
	 *
	 * @return void
	 */
	public function render() {
		$this->getOutput()->addWikiMsg( 'wss-missing-permissions-description' );
	}

	/**
	 * @inheritDoc
	 */
	public function getIdentifier(): string {
		return 'missing-permissions';
	}

	/**
	 * @inheritDoc
	 */
	public function getHeaderPrefix(): string {
		return "\u{1F512}";
	}

	/**
	 * @inheritDoc
	 */
	public function getNavigationPrefix(): string {
		return wfMessage( 'wss-missing-permissions-topnav' )->plain();
	}
}
