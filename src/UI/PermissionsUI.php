<?php


namespace PDP\UI;

use MediaWiki\MediaWikiServices;
use PDP\Form\PermissionsMatrixForm;
use PDP\SubmitCallback\PermissionsMatrixSubmitCallback;
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
        $namespace_constant = self::getNamespaces()[$this->getParameter()] ?? 0;

        $form = new PermissionsMatrixForm(
            $namespace_constant,
            $this->getOutput(),
            new PermissionsMatrixSubmitCallback( $this, $namespace_constant ),
            new PermissionsMatrixValidationCallback()
        );

        $form->show();
    }

    /**
     * Returns a key-value pair of namespace constants and their associated name.
     *
     * @return array|mixed
     */
    private static function getNamespaces() {
        try {
            $config = MediaWikiServices::getInstance()->getMainConfig();
            $namespaces = [ NS_MAIN => 'Main' ] + $config->get( 'CanonicalNamespaceNames' );
            $namespaces += \ExtensionRegistry::getInstance()->getAttribute( 'ExtensionNamespaces' );

            if ( is_array( $config->get( 'ExtraNamespaces' ) ) ) {
                $namespaces += $config->get( 'ExtraNamespaces' );
            }
        } catch(\Exception $e) {
            return [];
        }

        return array_flip( $namespaces );
    }
}