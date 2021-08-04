<?php

namespace WSS\UI;

/**
 * Class ManageSpaceUI
 * @package WSS\UI
 */
abstract class ManageSpaceUI extends SpacesUI {
	/**
	 * @inheritDoc
	 */
	public function ignoreParameterInNavigationHighlight(): bool {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getIdentifier(): string {
		return 'active-spaces';
	}
}
