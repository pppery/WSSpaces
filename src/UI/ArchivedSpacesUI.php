<?php

namespace WSS\UI;

use WSS\ArchivedSpacePager;

/**
 * Class ArchivedSpacesUI
 *
 * @package WSS\UI
 */
class ArchivedSpacesUI extends SpacesUI {
	/**
	 * @inheritDoc
	 */
	public function render() {
		$pager = new ArchivedSpacePager();

		$this->getOutput()->addParserOutput(
			$pager->getFullOutput()
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getIdentifier(): string {
		return 'archived-spaces';
	}
}
