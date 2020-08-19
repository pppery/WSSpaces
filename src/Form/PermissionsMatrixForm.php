<?php

namespace PDP\Form;

use ConfigException;
use MediaWiki\MediaWikiServices;
use PDP\Validation\PermissionsMatrixValidationCallback;

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
                'columns' => $this->getColumns(), // TODO
                'rows' => $this->getRows(),
                'default' => [], // TODO
                'validation-callback' => function($field, $data) {
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
        return true;
    }

    private function getColumns() {
        $columns = PermissionsMatrixValidationCallback::getValidRights();
        return array_combine($columns, $columns);
    }

    private function getRows() {
        $rows = PermissionsMatrixValidationCallback::getValidUserGroups();
        return array_combine($rows, $rows);
    }
}