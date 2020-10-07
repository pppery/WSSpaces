<?php

namespace WSS\Validation;

/**
 * Class FakeValidationCallback
 *
 * @package WSS\Validation
 */
class FakeValidationCallback extends AbstractValidationCallback {
    /**
     * Validates whether the given value is allowed for the given field.
     *
     * @param string $field
     * @param string|array $value
     * @param array $form_data
     * @return bool|string
     */
    public function validateField(string $field, $value, array $form_data) {
        return true;
    }
}