<?php

namespace PDP\UI;

use MediaWiki\Linker\LinkRenderer;
use OutputPage;
use PDP\Form\PermissionsMatrixForm;
use PDP\NamespaceRepository;
use PDP\Space;
use PDP\SubmitCallback\PermissionsMatrixSubmitCallback;
use PDP\Validation\PermissionsMatrixValidationCallback;

/**
 * Class PermissionsUI
 *
 * @package PDP\UI
 */
class PermissionsUI extends PDPUI {
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
            $this->getOutput()->addWikiMsg( 'pdp-permissions-core-namespace' );
        }

        $this->getOutput()->addWikiMsg( 'pdp-permissions-intro' );
        $this->showPermissionsMatrixForm();
    }

    /**
     * @inheritDoc
     */
    public function getModules(): array {
        return [ "ext.pdp.SpecialPermissions" ];
    }

    /**
     * @inheritDoc
     */
    public function getHeader(): string {
        $namespace = $this->getParameter();
        $space = Space::newFromName( $namespace );
        $display_name = $space ? $space->getDisplayName() : $namespace;

        return wfMessage( 'pdp-permissions-header', $display_name )->plain();
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
        return wfMessage( 'pdp-permissions-topnav' )->plain();
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