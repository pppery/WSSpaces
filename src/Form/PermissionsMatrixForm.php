<?php

namespace PDP\Form;

use OutputPage;
use PDP\SubmitCallback\SubmitCallback;
use PDP\PermissionsMatrix;
use PDP\Validation\PermissionsMatrixValidationCallback;
use PDP\Validation\AbstractValidationCallback;

class PermissionsMatrixForm extends AbstractForm {
    /**
     * @var string
     */
    private $namespace_constant;

    /**
     * PermissionsMatrixForm constructor.
     * @param string $namespace_constant
     * @param OutputPage $page
     * @param SubmitCallback $submit_callback
     * @param AbstractValidationCallback|null $validation_callback
     */
    public function __construct(
        string $namespace_constant,
        OutputPage $page,
        SubmitCallback $submit_callback,
        AbstractValidationCallback $validation_callback = null
    ) {
        $this->namespace_constant = $namespace_constant;

        parent::__construct( $page, $submit_callback, $validation_callback );
    }

    /**
     * Returns this form's descriptor.
     *
     * @return array
     */
    function getDescriptor(): array {
        return [
            'checkmatrix' => [
                'type' => 'checkmatrix',
                'columns' => $this->getColumns(), // TODO
                'rows' => $this->getRows(),
                'default' => $this->getDefault(),
                'validation-callback' => function( $field, $data ) {
                    return $this->getValidationCallback()->validateField('checkmatrix', $field, $data);
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
        return false;
    }

    private function getColumns() {
        $columns = PermissionsMatrixValidationCallback::getValidRights();
        return array_combine($columns, $columns);
    }

    private function getRows() {
        $rows = PermissionsMatrixValidationCallback::getValidUserGroups();
        return array_combine($rows, $rows);
    }

    private function getDefault() {
        $permissions = PermissionsMatrix::newFromNamespaceConstant( $this->namespace_constant );
        $permissions->setStringMode();

        return iterator_to_array( $permissions );
    }
}