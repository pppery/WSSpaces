<?php


namespace PDP\Validation;

use PDP\NamespaceRepository;
use PDP\Space;

/**
 * Class AddSpaceValidationCallback
 * @package PDP\Validation
 */
class AddSpaceValidationCallback extends AbstractValidationCallback {
    /**
     * Validates whether the given value is allowed for the given field.
     *
     * @param string $field The name of the field. Can be any arbitrary string.
     * @param string|array $value The value given to the field.
     * @param array $form_data Other data given to the form.
     * @return bool|string
     * @throws \ConfigException
     */
    public function validateField( string $field, $value, array $form_data ) {
        switch ( $field ) {
            case 'displayname':
                return $this->validateDisplayName( $value, $form_data );
            case 'namespace':
                return $this->validateNamespace( $value, $form_data );
            default:
                return false;
        }
    }

    /**
     * @param $value
     * @param array $form_data
     * @return bool|string
     */
    private function validateDisplayName( $value, array $form_data ) {
        $valid = ctype_alpha( str_replace( ' ', '', $value ) ) && !empty( $value );

        return $valid ? true : "A display name may only consist of letters and spaces";
    }

    /**
     * @param $value
     * @param array $form_data
     * @return bool|string
     * @throws \ConfigException
     */
    private function validateNamespace( $value, array $form_data ) {
        $valid = ctype_alpha( $value ) && !empty( $value );

        if ( !$valid ) {
            return "A namespace may only consist of letters";
        }

        $namespace_repository = new NamespaceRepository();
        $namespaces = $namespace_repository->getAllNamespaces();

        $space = Space::newFromName( $value );

        if ( in_array( $value, $namespaces ) || $space !== false ) {
            return "This namespace is already in use. Please choose a different name.";
        }

        return true;
    }
}