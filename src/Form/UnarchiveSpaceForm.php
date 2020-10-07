<?php

namespace WSS\Form;

use OutputPage;
use WSS\Space;
use WSS\SubmitCallback\SubmitCallback;
use WSS\Validation\AbstractValidationCallback;

class UnarchiveSpaceForm extends AbstractForm {
    /**
     * @var Space
     */
    private $space;

    public function __construct(
        Space $space,
        OutputPage $page, 
        SubmitCallback $submit_callback, 
        AbstractValidationCallback $validation_callback = null ) {
        $this->space = $space;

        parent::__construct( $page, $submit_callback, $validation_callback );
    }

    /**
     * Returns this form's descriptor.
     *
     * @return array
     */
    public function getDescriptor(): array {
        return [
            'namespaceid' => [
                'label-message' => 'wss-manage-space-form-namespaceid-label',
                'type' => 'text',
                'disabled' => true,
                'default' => $this->space->getId(),
                'validation-callback' => function( $field, $data ) {
                    return $this->getValidationCallback()->validateField( 'namespace_id', $field, $data );
                }
            ],
            'namespacename' => [
                'label-message' => 'wss-manage-space-form-namespacename-label',
                'type' => 'text',
                'disabled' => true,
                'default' => $this->space->getName(),
                'validation-callback' => function( $field, $data ) {
                    return $this->getValidationCallback()->validateField( 'namespace_name', $field, $data );
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
        return 'wss_manage_space';
    }

    /**
     * Returns this form's submit text.
     *
     * @return string
     */
    public function getSubmitText(): string {
        return wfMessage( 'wss-unarchive-space-form-submit-text' )->plain();
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