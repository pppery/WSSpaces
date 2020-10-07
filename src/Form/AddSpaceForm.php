<?php

namespace WSS\Form;

/**
 * Class AddSpaceForm
 * @package WSS\Form
 */
class AddSpaceForm extends AbstractForm {
    /**
     * @inheritDoc
     */
    public function getDescriptor(): array {
        return [
            'displayname' => [
                'label-message' => 'wss-add-space-form-displayname-label',
                'type' => 'text',
                'size' => 32,
                'maxlength' => 64,
                'required' => true,
                'validation-callback' => function( $field, $data ) {
                    return $this->getValidationCallback()->validateField( 'displayname', $field, $data );
                }
            ],
            'description' => [
                'label-message' => 'wss-add-space-form-description-label',
                'type' => 'textarea',
                'rows' => 4,
                'required' => true,
                'validation-callback' => function( $field, $data ) {
                    return $this->getValidationCallback()->validateRequired( $field );
                }
            ],
            'namespace' => [
                'label-message' => 'wss-add-space-form-namespace-label',
                'help-message' => 'wss-add-space-form-namespace-help',
                'type' => 'text',
                'size' => 32,
                'maxlength' => 24,
                'required' => true,
                'validation-callback' => function( $field, array $data ) {
                    return $this->getValidationCallback()->validateField( 'namespace', $field, $data );
                }
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return "add_space_form";
    }

    /**
     * @inheritDoc
     */
    public function getSubmitText(): string {
        return wfMessage( 'wss-add-space-form-submit-text' )->plain();
    }

    /**
     * @inheritDoc
     */
    public function isDestructive(): bool {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function showCancel(): bool {
        return true;
    }

    public function cancelTarget(): \Title {
        return \Title::newFromText( "Special:ManageSpace" );
    }
}