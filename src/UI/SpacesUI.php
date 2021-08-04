<?php

namespace WSS\UI;

/**
 * Class SpacesUI
 *
 * @package WSS\UI
 */
abstract class SpacesUI extends WSSUI {
	/**
	 * @inheritDoc
	 */
	public function getHeaderPrefix(): string {
		return "\u{1F4D0}";
	}

	/**
	 * @inheritDoc
	 */
	public function getNavigationPrefix(): string {
		return wfMessage( 'wss-space-topnav-prefix' )->plain();
	}

	/**
	 * @inheritDoc
	 */
	public function getModules(): array {
		return array_merge( [ 'ext.wss.Spaces' ], $this->getConditionalModules() );
	}

	/**
	 * Sets a flag to allow for a wss_callback query parameter.
	 */
	public function setAllowCallback() {
		$this->getOutput()->getRequest()->getSession()->set( "callback-allowed", true );
	}

	/**
	 * Returns an array of modules that are conditional.
	 *
	 * @return array
	 */
	public function getConditionalModules(): array {
		$request = $this->getOutput()->getRequest();

		$session = $request->getSession();
		$callback_allowed = $session->exists( "callback-allowed" );

		if ( !$callback_allowed ) {
			return [];
		}

		$callback = $request->getVal( 'wss_callback' );

		if ( $callback === null ) {
			return [];
		}

		$session->remove( "callback-allowed" );

		switch ( $callback ) {
			case "created":
				return [ "ext.wss.AddSpaceSuccess" ];
			case "archived":
				return [ "ext.wss.ArchiveSpaceSuccess" ];
			case "unarchived":
				return [ "ext.wss.UnarchiveSpaceSuccess" ];
			default:
				return [];
		}
	}
}
