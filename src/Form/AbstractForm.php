<?php

namespace PDP\Form;

use HTMLForm;
use OutputPage;
use PDP\Handler\SubmitCallback;
use PDP\Validation\FakeValidationCallback;
use PDP\Validation\ValidationCallback;

abstract class AbstractForm {
    /**
     * @var ValidationCallback
     */
    private $validation_callback;

    /**
     * @var SubmitCallback
     */
    private $submit_callback;

    /**
     * @var OutputPage
     */
    private $page;

    /**
     * AbstractForm constructor.
     *
     * @param OutputPage $page
     * @param SubmitCallback $submit_callback
     * @param ValidationCallback|null $validation_callback
     */
    public function __construct( OutputPage $page, SubmitCallback $submit_callback, ValidationCallback $validation_callback = null ) {
        $this->page = $page;

        $this->setSubmitCallback( $submit_callback );
        $this->setValidationCallback( $validation_callback ?? new FakeValidationCallback() );
    }

    /**
     * Sets this form's submit callback.
     *
     * @param SubmitCallback $callback
     */
    public function setSubmitCallback( SubmitCallback $callback ) {
        $this->submit_callback = $callback;
    }

    /**
     * Sets this form's validation callback.
     *
     * @param ValidationCallback $callback
     */
    public function setValidationCallback( ValidationCallback $callback ) {
        $this->validation_callback = $callback;
    }

    /**
     * Returns the submit callback for this form.
     *
     * @return SubmitCallback
     */
    public function getSubmitCallback(): SubmitCallback {
        return $this->submit_callback;
    }

    /**
     * Returns the validation callback for this form.
     *
     * @return ValidationCallback
     */
    public function getValidationCallback(): ValidationCallback {
        return $this->validation_callback;
    }

    /**
     * Returns the form.
     *
     * @return HTMLForm
     */
    public function getForm(): HTMLForm {
        $form = HTMLForm::factory( 'ooui', $this->getDescriptor(), $this->page->getContext() );

        $form->setMessagePrefix( $this->getName() );
        $form->setSubmitText( $this->getSubmitText() );
        $form->setSubmitCallback( [ $this->getSubmitCallback(), 'onSubmit' ] );

        if ( $this->isDestructive() ) {
            $form->setSubmitDestructive();
        }

        return $form;
    }

    /**
     * Shows this form.
     *
     * @return void
     */
    public function show() {
        $this->getForm()->show();
    }

    /**
     * Returns this form's descriptor.
     *
     * @return array
     */
    abstract public function getDescriptor(): array;

    /**
     * Returns this form's name.
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * Returns this form's submit text.
     *
     * @return string
     */
    abstract public function getSubmitText(): string;

    /**
     * Returns true if and only if this form is (or can be) destructive.
     *
     * @return bool
     */
    abstract public function isDestructive(): bool;
}