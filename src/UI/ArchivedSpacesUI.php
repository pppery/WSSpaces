<?php


namespace PDP\UI;

use PDP\ArchivedSpacePager;

/**
 * Class ArchivedSpacesUI
 *
 * @package PDP\UI
 */
class ArchivedSpacesUI extends SpacesUI {
    /**
     * @inheritDoc
     */
    function render() {
        $pager = new ArchivedSpacePager();

        $this->getOutput()->addParserOutput(
            $pager->getFullOutput()
        );
    }

    /**
     * @inheritDoc
     */
    function getIdentifier(): string {
        return 'archived-spaces';
    }
}