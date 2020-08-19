<?php


namespace PDP\UI;

use MediaWiki\MediaWikiServices;
use PDP\Form\PermissionsMatrixForm;
use PDP\Handler\PermissionsMatrixHandler;
use PDP\Validation\PermissionsMatrixValidationCallback;

/**
 * Class PermissionsUI
 *
 * @package PDP\UI
 */
class PermissionsUI extends PDPUI {
    /**
     * @inheritDoc
     */
    public function render() {
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
        return wfMessage( 'pdp-permissions-header', $this->getParameter() )->plain();
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
     */
    public function getNavigationItems(): array {
       $namespaces = PermissionsMatrixValidationCallback::getValidSpaces();
       $result = [];

       foreach ($namespaces as $namespace) {
           $result[$namespace] = "Special:Permissions/$namespace";
       }

       return $result;
    }

    /**
     * Shows the PermissionsMatrix form.
     */
    private function showPermissionsMatrixForm() {
        $form = new PermissionsMatrixForm(
            $this->getOutput(),
            new PermissionsMatrixHandler(),
            new PermissionsMatrixValidationCallback()
        );

        $form->show();
    }


}