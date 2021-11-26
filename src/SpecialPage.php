<?php

namespace WSS;

use ErrorPageError;
use PermissionsError;

abstract class SpecialPage extends \SpecialPage {
	/**
	 * @inheritDoc
	 * @throws PermissionsError
	 * @throws ErrorPageError
	 */
	public function execute( $parameter ) {
		$this->setHeaders();
		$this->checkPermissions();

		$security_level = $this->getLoginSecurityLevel();

		if ( $security_level ) {
			$proceed = $this->checkLoginSecurityLevel( $security_level );

			if ( !$proceed ) {
				return;
			}
		}

		if ( $this->preExecute() === false ) {
			return;
		}

		$parameter = $parameter ?? '';

		$this->doExecute( $parameter );
		$this->postExecuteCleanup();
	}

	/**
	 * Cleans up the special page after the main execute method. Can for instance be used to set headers or commit
	 * some database transactions.
	 *
	 * @return void
	 */
	public function postExecuteCleanup() {
	}

	/**
	 * Does some checks before the special page is even shown.
	 *
	 * @return bool
	 */
	public function preExecute(): bool {
		return true;
	}

	/**
	 * The main method that gets invoked upon successfully loading the special page, after preExecuteChecks have
	 * been performed.
	 *
	 * @param string $parameter
	 * @return void
	 */
	abstract public function doExecute( string $parameter );
}
