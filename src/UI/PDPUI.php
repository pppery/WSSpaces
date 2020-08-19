<?php

namespace PDP\UI;

use HtmlArmor;
use MediaWiki\Linker\LinkRenderer;
use OutputPage;

/**
 * Class PDPUI
 *
 * A PDPUI class manipulates the given OutputPage object to create a user interface.
 *
 * @package PDP\UI
 */
abstract class PDPUI {
    const GLOBAL_MODULES = [
        "ext.pdp.Global"
    ];

    private static $queued = false;

    /**
     * @var OutputPage
     */
    private $page;

    /**
     * @var LinkRenderer
     */
    private $link_renderer;

    /**
     * @var string
     */
    private $parameter = '';

    /**
     * @var array
     */
    private $modules = [];

    /**
     * Returns true if and only if PDPUI is queued to be rendered.
     *
     * @return bool
     */
    public static function isQueued(): bool {
        return self::$queued;
    }

    /**
     * PDPUI constructor.
     *
     * @param OutputPage $page
     * @param LinkRenderer $link_renderer
     */
    public function __construct( OutputPage $page, LinkRenderer $link_renderer ) {
        self::$queued = true;

        $this->page = $page;
        $this->link_renderer = $link_renderer;

        $this->page->enableOOUI();
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
    public function getParameter(): string {
        return str_replace("_", " ", $this->parameter);
    }

    /**
     * Returns the link renderer for this UI.
     *
     * @return LinkRenderer
     */
    public function getLinkRenderer(): LinkRenderer {
        return $this->link_renderer;
    }
    
    /**
     * Renders the UI.
     *
     * @return void
     */
    public function execute() {
        $this->preRender();
        $this->render();
        $this->postRender();
    }

    /**
     * Executed before the main render() method is run.
     */
    private function preRender() {
        $this->getOutput()->preventClickjacking();
    }

    /**
     * Executed after the main render() method has been run.
     */
    private function postRender() {
        $this->loadModules();
        $this->renderHeader();
        $this->renderNavigation();
    }

    /**
     * Loads the specified modules.
     */
    private function loadModules() {
        $modules = array_merge($this->getModules(), self::GLOBAL_MODULES);
        $this->getOutput()->addModules( $modules );
    }

    /**
     * Renders the header specified via $this->getHeader().
     */
    private function renderHeader() {
        $this->getOutput()->setPageTitle(
            \Xml::element("div",
                ["class" => "pdp title"],
                $this->getHeaderPrefix() . " " . $this->getHeader()
            )
        );
    }

    /**
     * Renders the navigation menu.
     */
    private function renderNavigation() {
        $link_definitions = $this->getNavigationItems();

        if (empty($link_definitions)) {
            return;
        }

        $links = array_map(function($key, $value) {
            $title = \Title::newFromText($value);

            if ($this->getParameter() === $key) {
                return \Xml::tags('strong', null, $key);
            }

            return $this->getLinkRenderer()->makeLink($title, new HtmlArmor($key));
        }, array_keys($link_definitions), array_values($link_definitions));

        $nav = wfMessage( 'parentheses' )
            ->rawParams($this->getOutput()->getLanguage()->pipeList($links))
            ->text();
        $nav = $this->getNavigationPrefix() . " $nav";
        $nav = \Xml::tags('div', ['class' => 'mw-pdp-topnav'], $nav);
        $this->getOutput()->setSubtitle($nav);
    }

    /**
     * Returns the navigation prefix shown on the navigation menu.
     *
     * @return string
     */
    public function getNavigationPrefix(): string {
        return '';
    }

    /**
     * Returns an array of modules that must be loaded.
     *
     * @return array
     */
    public function getModules(): array {
        return [];
    }

    /**
     * Returns the elements in the navigation menu. These elements take the form of a key-value pair,
     * where the key is the system message shown as the hyperlink, and the value is the page name. The
     * key is prepended with the prefix "pdp-topnav-".
     *
     * @return array
     */
    public function getNavigationItems(): array {
        return [];
    }

    /**
     * Returns the text that will be prepended to the title. Usually used for title icons.
     *
     * @return string
     */
    public function getHeaderPrefix(): string {
        return '';
    }

    /**
     * Renders the UI.
     *
     * @return void
     */
    abstract function render();

    /**
     * Returns the header text shown in the UI.
     *
     * @return string
     */
    abstract function getHeader(): string;
}