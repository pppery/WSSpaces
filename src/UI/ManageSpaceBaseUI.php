<?php

namespace PDP\UI;

use PDP\SpacePager;

/**
 * Class ManageSpaceBaseUI
 * @package PDP\UI
 */
class ManageSpaceBaseUI extends ManageSpaceUI {
    /**
     * Renders the UI.
     *
     * @return void
     */
    function render() {
        $pager = new SpacePager();

        $this->getOutput()->addParserOutput(
            $pager->getFullOutput()
        );
    }
}