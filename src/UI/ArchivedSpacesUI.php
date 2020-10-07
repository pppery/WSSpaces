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