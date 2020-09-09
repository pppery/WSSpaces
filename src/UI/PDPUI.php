<?php

namespace PDP\UI;

use HtmlArmor;
use MediaWiki\Linker\LinkRenderer;
use MWException;
use OutputPage;
use PDP\NamespaceRepository;
use PDP\Space;

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

    /**
     * @var bool
     */
    private static $queued = false;

    /**
     * @var string
     */
    private static $parameter = '';

    /**
     * @var PDPUI
     */
    private static $ui;

    /**
     * @var OutputPage
     */
    private $page;

    /**
     * @var LinkRenderer
     */
    private $link_renderer;

    /**
     * @var array
     */
    private $modules = [];

    /**
     * Returns true if and only if a PDPUI is queued to be rendered.
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
     * @throws MWException
     */
    public function __construct( OutputPage $page, LinkRenderer $link_renderer ) {
        self::$ui = $this;

        $this->queue();

        $this->page = $page;
        $this->link_renderer = $link_renderer;

        $this->page->enableOOUI();
    }

    /**
     * PHPUI destructor.
     */
    public function __destruct() {
        $this->unqueue();
    }

    /**
     * Sets the sidebar.
     *
     * @param $bar
     * @throws \ConfigException
     */
    public static function setSidebar( &$bar ) {
        $bar = $bar + self::$ui->getSidebarItems();
    }

    /**
     * Returns the items that will be added to the sidebar.
     *
     * @return mixed
     * @throws \ConfigException
     */
    public function getSidebarItems(): array {
        $space = $this->getParameter();
        $spaces = ( new NamespaceRepository() )->getSpaces();

        if ( !in_array( $space, $spaces ) ) {
            // We cannot edit regular namespaces.
            return [];
        }

        $display_name = ucfirst( Space::newFromName( $space )->getDisplayName() );

        $bar[wfMessage( 'pdp-space-administration-sidebar-header', $display_name )->plain()][] = [
            'text' => wfMessage( 'pdp-edit-space-details' ),
            'href' => \Title::newFromText( "ManageSpace/$space", NS_SPECIAL )->getFullUrlForRedirect(),
            'id'   => 'pdp-permissions-special',
            'active' => ''
        ];

        $bar[wfMessage( 'pdp-space-administration-sidebar-header', $display_name )->plain()][] = [
            'text' => wfMessage( 'pdp-edit-space-permissions' ),
            'href' => \Title::newFromText( "Permissions/$space", NS_SPECIAL )->getFullUrlForRedirect(),
            'id'   => 'pdp-permissions-special',
            'active' => ''
        ];

        return $bar;
    }

    /**
     * Returns the parameter (or subpage name) from this page.
     *
     * @return string
     */
    public static function getParameter(): string {
        return str_replace( "_", " ", self::$parameter );
    }

    /**
     * Sets the parameter (or subpage name) for this page.
     *
     * @param string $parameter
     */
    public function setParameter( string $parameter ) {
        self::$parameter = $parameter;
    }

    /**
     * Adds a modules to be loaded.
     *
     * @param string $module
     */
    public function addModule(string $module ) {
        $this->modules[] = $module;
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
        $this->getOutput()->clearHTML();
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
        $modules = array_merge($this->getModules(), $this->modules, self::GLOBAL_MODULES);
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

        $links = array_map( function( $key, $value ) {
            $title = \Title::newFromText( $value );

            $page_title = $this->getOutput()->getTitle()->getFullText();
            $page_name  = $this->ignoreParameterInNavigationHighlight() ?
                explode( '/', $page_title )[0] :
                $page_title;

            $link_text = htmlspecialchars( $key );

            if ( $page_name === $value || $this->getParameter() === $link_text ) {
                return \Xml::tags( 'strong', null, $link_text );
            }

            return $this->getLinkRenderer()->makeLink( $title, new HtmlArmor( $link_text ) );
        }, array_keys( $link_definitions ), array_values( $link_definitions ) );

        $nav = wfMessage( 'parentheses' )
            ->rawParams($this->getOutput()->getLanguage()->pipeList( $links ) )
            ->text();
        $nav = $this->getNavigationPrefix() . " $nav";
        $nav = \Xml::tags( 'div', ['class' => 'mw-pdp-topnav'], $nav );
        $this->getOutput()->setSubtitle( $nav );
    }

    /**
     * Locks out other classes from creating a PDPUI object.
     */
    private function queue() {
        self::$queued = true;
    }

    /**
     * Enables other classes to create a PDPUI object.
     */
    private function unqueue() {
        self::$queued = false;
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
     * Returns whether or not to ignore the parameter when highlighting navigation items (to show you are on a page).
     *
     * @return bool
     */
    public function ignoreParameterInNavigationHighlight(): bool {
        return false;
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
     * Returns the header text shown in the UI.
     *
     * @return string
     */
    public function getHeader(): string {
        return wfMessage( 'pdp-' . $this->getIdentifier() . '-header' )->plain();
    }

    /**
     * Renders the UI.
     *
     * @return void
     */
    abstract function render();

    /**
     * Returns the identifier used in some system messages.
     *
     * @return string
     */
    abstract function getIdentifier(): string;
}