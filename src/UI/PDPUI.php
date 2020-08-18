<?php

namespace PDP\UI;

use OutputPage;

/**
 * Class PDPUI
 *
 * A PDPUI class manipulates the given OutputPage object to create a user interface.
 *
 * @package PDP\UI
 */
abstract class PDPUI {
    /**
     * @var OutputPage
     */
    private $page;

    /**
     * @var string
     */
    private $parameter;

    /**
     * PDPUI constructor.
     *
     * @param OutputPage $page
     */
    public function __construct( OutputPage $page ) {
        $this->page = $page;
    }

    /**
     * Sets the parameter (or subpage name) for this page.
     *
     * @param string $parameter
     */
    public function setParameter( string $parameter ) {
        $this->parameter = $parameter;
    }

    /**
     * Returns the output page for this UI.
     *
     * @return OutputPage
     */
    public function getOutput(): OutputPage {
        return $this->page;
    }

    /**
     * Returns the parameter (or subpage name) from this page.
     *
     * @return string
     */
    public function getParameter() {
        return $this->parameter;
    }
    
    /**
     * Renders the UI.
     *
     * @return void
     */
    abstract function execute();
}