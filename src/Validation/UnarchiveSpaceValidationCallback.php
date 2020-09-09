<?php


namespace PDP\Validation;


use PDP\NamespaceRepository;

class UnarchiveSpaceValidationCallback extends AbstractValidationCallback {
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
        switch( $field ) {
            case "namespace_name":
                return in_array( $value, ( new NamespaceRepository() )->getArchivedSpaces() );
            case "namespace_id":
                return in_array( $value, ( new NamespaceRepository() )->getArchivedSpaces( true ) );
            default:
                return false;
        }
    }
}