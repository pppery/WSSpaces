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
     */
    public function render() {
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
     * @throws \ConfigException
     */
    public function getHeader(): string {
        $space = Space::newFromConstant( $this->getParameter() );
        return wfMessage( 'wss-permissions-header', $space->getName() )->parse();
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
     * Shows the PermissionsMatrix form.
     */
    private function showPermissionsMatrixForm() {
        $namespaces = $this->namespaces->getSpaces();
        $parameter = $this->getParameter();

        if ( !isset( $namespaces[$parameter] ) ) {
            throw new \InvalidArgumentException( "The namespace constant '{$parameter}' is invalid." );
        }

        $form = new PermissionsMatrixForm(
            $parameter,
            $this->getOutput(),
            new PermissionsMatrixSubmitCallback( $this, $parameter ),
            new PermissionsMatrixValidationCallback()
        );

        $form->show();
    }
}