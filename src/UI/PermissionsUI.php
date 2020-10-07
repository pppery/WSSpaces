<?php

namespace WSS\UI;

use MediaWiki\Linker\LinkRenderer;
use OutputPage;
use WSS\Form\PermissionsMatrixForm;
use WSS\NamespaceRepository;
use WSS\Space;
use WSS\SubmitCallback\PermissionsMatrixSubmitCallback;
use WSS\Validation\PermissionsMatrixValidationCallback;

/**
 * Class PermissionsUI
 *
 * @package WSS\UI
 */
class PermissionsUI extends WSSUI {
    /**
     * @var NamespaceRepository
     */
    private $namespaces;

    public function __construct( OutputPage $page, LinkRenderer $link_renderer ) {
        $this->namespaces = new NamespaceRepository();

        parent::__construct($page, $link_renderer);
    }

    /**
     * @inheritDoc
     * @throws \ConfigException
     */
    public function render() {
        if ( !in_array( $this->getParameter(), $this->namespaces->getSpaces() ) ) {
            $this->getOutput()->addWikiMsg( 'wss-permissions-core-namespace' );
        }

        $this->getOutput()->addWikiMsg( 'wss-permissions-intro' );
        $this->showPermissionsMatrixForm();
    }

    /**
     * @inheritDoc
     */
    public function getModules(): array {
        return [ "ext.wss.SpecialPermissions" ];
    }

    /**
     * @inheritDoc
     */
    public function getHeader(): string {
        $namespace = $this->getParameter();
        $space = Space::newFromName( $namespace );
        $display_name = $space ? $space->getDisplayName() : $namespace;

        return wfMessage( 'wss-permissions-header', $display_name )->plain();
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier(): string {
        return 'permissions';
    }

    /**
     * @inheritDoc
     */
    public function getHeaderPrefix(): string {
        return "\u{1F680}";
    }

    /**
     * @inheritDoc
     */
    public function getNavigationPrefix(): string {
        return wfMessage( 'wss-permissions-topnav' )->plain();
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function getNavigationItems(): array {
        if ( ( Space::newFromName( $this->getParameter() ) ) ) {
            return [];
        }

        $namespaces = ( new NamespaceRepository() )->getCoreNamespaces();

        $result = [];
        foreach ($namespaces as $namespace) {
           $space = Space::newFromName( $namespace );
           $display_name = $space ? $space->getDisplayName() : $namespace;

           $result[$display_name] = "Special:Permissions/$namespace";
        }

        return $result;
    }

    /**
     * Shows the PermissionsMatrix form.
     * @throws \InvalidArgumentException
     * @throws \ConfigException
     */
    private function showPermissionsMatrixForm() {
        $namespaces = $this->namespaces->getNamespaces( true );

        if ( !isset( $namespaces[$this->getParameter()] ) ) {
            throw new \InvalidArgumentException( "The namespace '{$this->getParameter()}' is invalid." );
        }

        $namespace_constant = $namespaces[$this->getParameter()];

        $form = new PermissionsMatrixForm(
            $namespace_constant,
            $this->getOutput(),
            new PermissionsMatrixSubmitCallback( $this, $namespace_constant ),
            new PermissionsMatrixValidationCallback()
        );

        $form->show();
    }
}