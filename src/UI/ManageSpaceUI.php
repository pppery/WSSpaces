<?php


namespace PDP\UI;

/**
 * Class ManageSpaceUI
 * @package PDP\UI
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
        return 'manage-space';
    }
}