<?php

namespace WSS\UI;

use WSS\SpacePager;

/**
 * Class ManageSpaceBaseUI
 * @package WSS\UI
 */
class ManageSpaceBaseUI extends ManageSpaceUI {
	/**
	 * @inheritDoc
	 */
	public function render() {
		$pager = new SpacePager();

		$this->getOutput()->addParserOutput(
			$pager->getFullOutput()
		);
	}
}
