<?php

namespace WSS;

/**
 * Class WSSPager
 *
 * @package WSS
 */
abstract class Pager extends \TablePager {
	/**
	 * @inheritDoc
	 */
	public function getTableClass() {
		return parent::getTableClass() . ' wss-table';
	}
}
