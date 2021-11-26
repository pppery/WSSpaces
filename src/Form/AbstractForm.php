<?php

namespace WSS\Form;

use HTMLForm;
use OutputPage;
use WSS\SubmitCallback\SubmitCallback;
use WSS\Validation\AbstractValidationCallback;
use WSS\Validation\FakeValidationCallback;

abstract class AbstractForm {
	/**
	 * @var AbstractValidationCallback
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
	 * @var HTMLForm
	 */
	private $form;

	/**
	 * AbstractForm constructor.
	 *
	 * @param OutputPage $page
	 * @param SubmitCallback $submit_callback
	 * @param AbstractValidationCallback|null $validation_callback
	 */
	public function __construct(
		OutputPage $page,
		SubmitCallback $submit_callback,
		AbstractValidationCallback $validation_callback = null
	) {
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
	 * @param AbstractValidationCallback $callback
	 */
	public function setValidationCallback( AbstractValidationCallback $callback ) {
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
	 * @return AbstractValidationCallback
	 */
	public function getValidationCallback(): AbstractValidationCallback {
		return $this->validation_callback;
	}

	/**
	 * Returns whether or not to show a cancel button.
	 *
	 * @return bool
	 */
	public function showCancel(): bool {
		return false;
	}

	/**
	 * Returns an array of buttons to be added.
	 *
	 * @return array
	 */
	public function getButtons(): array {
		return [];
	}

	/**
	 * Returns the Title to redirect to when the user presses 'cancel'.
	 *
	 * @return \Title
	 */
	public function cancelTarget(): \Title {
		// Main page
		return \Title::newFromID( 1 );
	}

	/**
	 * Returns the form.
	 *
	 * @return HTMLForm
	 */
	public function getForm(): HTMLForm {
		if ( isset( $this->form ) ) {
			return $this->form;
		}

		$this->form = HTMLForm::factory( 'ooui', $this->getDescriptor(), $this->page->getContext() );

		$this->form->setMessagePrefix( $this->getName() );
		$this->form->setSubmitText( $this->getSubmitText() );
		$this->form->setSubmitCallback( [ $this->getSubmitCallback(), 'onSubmit' ] );

		if ( $this->isDestructive() ) {
			$this->form->setSubmitDestructive();
		}

		foreach ( $this->getButtons() as $button ) {
			$this->form->addButton( $button );
		}

		$this->form->setCancelTarget( $this->cancelTarget() );
		$this->form->showCancel( $this->showCancel() );
		$this->form->setTokenSalt( "wss" );

		return $this->form;
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
