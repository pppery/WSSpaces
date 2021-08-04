<?php

namespace WSS\UI;

use WSS\Form\UnarchiveSpaceForm;
use WSS\Space;
use WSS\SubmitCallback\UnarchiveSpaceSubmitCallback;

/**
 * Class UnarchiveSpaceUI
 *
 * @package WSS\UI
 */
class UnarchiveSpaceUI extends SpacesUI {
	/**
	 * @inheritDoc
	 * @throws \ConfigException
	 */
	function render() {
		$namespace_constant = (int)$this->getParameter();
		$space = Space::newFromConstant( $namespace_constant );

		$form = new UnarchiveSpaceForm(
			$space,
			$this->getOutput(),
			new UnarchiveSpaceSubmitCallback( $this )
		);

		$form->show();
	}

	/**
	 * @inheritDoc
	 */
	function getIdentifier(): string {
		return 'unarchive-space';
	}
}
