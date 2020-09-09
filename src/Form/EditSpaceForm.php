<?php

namespace PDP\Form;

use OutputPage;
use PDP\Space;
use PDP\SubmitCallback\SubmitCallback;
use PDP\Validation\AbstractValidationCallback;

class EditSpaceForm extends AbstractForm {
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
                'label-message' => 'pdp-manage-space-form-namespaceid-label',
                'type' => 'text',
                'disabled' => true,
                'default' => $this->space->getId()
            ],
            'namespacename' => [
                'label-message' => 'pdp-manage-space-form-namespacename-label',
                'type' => 'text',
                'disabled' => true,
                'default' => $this->space->getName()
            ],
            'createdby' => [
                'label-message' => 'pdp-manage-space-form-createdby-label',
                'type' => 'text',
                'disabled' => true,
                'default' => $this->space->getOwner()->getName()
            ],
            'displayname' => [
                'label-message' => 'pdp-add-space-form-displayname-label',
                'type' => 'text',
                'size' => 32,
                'maxlength' => 64,
                'required' => true,
                'default' => $this->space->getDisplayName(),
                'validation-callback' => function( $field, $data ) {
                    return $this->getValidationCallback()->validateField( 'displayname', $field, $data );
                }
            ],
            'description' => [
                'label-message' => 'pdp-add-space-form-description-label',
                'type' => 'textarea',
                'rows' => 4,
                'required' => true,
                'default' => $this->space->getDescription(),
                'validation-callback' => function( $field, $data ) {
                    return $this->getValidationCallback()->validateRequired( $field );
                }
            ],
            'administrators' => [
                'label-message' => 'pdp-manage-space-form-administrators-label',
                'type' => 'usersmultiselect',
                'required' => false,
                'default' => implode( "\n", $this->space->getSpaceAdministrators() ),
                'exists' => true
            ]
        ];
    }

    /**
     * Returns this form's name.
     *
     * @return string
     */
    public function getName(): string {
        return 'pdp_manage_space';
    }

    /**
     * Returns this form's submit text.
     *
     * @return string
     */
    public function getSubmitText(): string {
        return wfMessage( 'pdp-manage-space-form-submit-text' )->plain();
    }

    /**
     * Returns true if and only if this form is (or can be) destructive.
     *
     * @return bool
     */
    public function isDestructive(): bool {
        return false;
    }
}