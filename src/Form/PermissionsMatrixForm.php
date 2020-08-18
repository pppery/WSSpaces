<?php

namespace PDP\Form;

use ConfigException;
use MediaWiki\MediaWikiServices;

class PermissionsMatrixForm extends AbstractForm {
    /**
     * Returns this form's descriptor.
     *
     * @return array
     * @throws ConfigException
     */
    function getDescriptor(): array {
        return [];
    }

    /**
     * Returns this form's name.
     *
     * @return string
     */
    public function getName(): string {
        return 'permissions_matrix_form';
    }

    /**
     * Returns this form's submit text.
     *
     * @return string
     */
    public function getSubmitText(): string {
        return wfMessage( 'pdp-permissions-matrix-submit-text' );
    }

    /**
     * Returns true if and only if this form is (or can be) destructive.
     *
     * @return bool
     */
    public function isDestructive(): bool {
        return true;
    }

    /**
     * Returns a list of key-value pairs where the key is the namespace text and the value is the namespace ID.
     *
     * @return array
     * @throws ConfigException
     */
    private function getNamespaces(): array {
        $config = MediaWikiServices::getInstance()->getMainConfig();

        $namespaces = [ NS_MAIN => 'Main' ] + $config->get( 'CanonicalNamespaceNames' );
        $namespaces += \ExtensionRegistry::getInstance()->getAttribute( 'ExtensionNamespaces' );

        if ( is_array( $config->get( 'ExtraNamespaces' ) ) ) {
            $namespaces += $config->get( 'ExtraNamespaces' );
        }

        foreach ( $namespaces as $constant => &$name ) {
            $name = str_replace( '_', ' ', $name );
        }

        return array_flip( $namespaces );
    }
}