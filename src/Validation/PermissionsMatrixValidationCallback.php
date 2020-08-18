<?php


namespace PDP\Validation;


class PermissionsMatrixValidationCallback implements ValidationCallback {
    /**
     * Validates whether the given value is allowed for the given field.
     *
     * @param string $field The name of the field. Can be any arbitrary string.
     * @param string|array $value The value given to the field.
     * @param array $form_data Other data given to the form.
     * @return bool|string
     */
    public function validateField( string $field, $value, array $form_data ) {
        // TODO: Implement validateField() method.
    }
}