<?php

namespace PDP\UI;

use PDP\SpacePager;

/**
 * Class ManageSpaceBaseUI
 * @package PDP\UI
 */
class ManageSpaceBaseUI extends ManageSpaceUI {
    /**
     * @inheritDoc
     */
    function render() {
        $pager = new SpacePager();

        $this->getOutput()->addParserOutput(
            $pager->getFullOutput()
        );
    }
}