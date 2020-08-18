<?php

namespace PDP\Form;

class PermissionsMatrixForm extends AbstractForm {
    /**
     * Returns this form's descriptor.
     *
     * @return array
     */
    function getDescriptor(): array {
        return [
            'checkmatrix' => [
                'type' => 'checkmatrix',
                'class' => 'HTMLCheckMatrix',
                'columns' => [], // TODO
                'rows' => [], // TODO
                'validation-callback' => function( $value, $data ) {
                    return $this->getValidationCallback()->validateField( 'permissions-matrix', $value, $data );
                }
            ]
        ];
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
}