<?php

namespace WSS\UI;

class InvalidPageUI extends WSSUI {
	/**
	 * Renders the UI.
	 *
	 * @return void
	 */
	public function render() {
		$this->getOutput()->addWikiMsg( 'wss-invalid-page-description' );
	}

	/**
	 * @inheritDoc
	 */
	public function getIdentifier(): string {
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
	public function getNavigationPrefix(): string {
		return wfMessage( 'wss-invalidpage-topnav' )->plain();
	}
}
