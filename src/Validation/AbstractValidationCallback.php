<?php

namespace WSS\Validation;

use MediaWiki\MediaWikiServices;

abstract class AbstractValidationCallback {
	/* A DBLoadBalancer for database access */
	protected $dbLoadBalancer = null;

	/**
	 * Get a DBLoadBalancer for database access
	 *
	 * @return DBLoadBalancer
	 */
	protected function getDBLoadBalancer()
	{
		if (null === $this->dbLoadBalancer) {
			$this->dbLoadBalancer = MediaWikiServices::getInstance()->getDBLoadBalancer();
		}
		return $this->dbLoadBalancer;
	}

	/**
	 * Validates whether the given value is allowed for the given field.
	 *
	 * @param string $field The name of the field. Can be any arbitrary string.
	 * @param string|array $value The value given to the field.
	 * @param array $form_data Other data given to the form.
	 * @return bool|string
	 */
	abstract public function validateField( string $field, $value, array $form_data );

	/**
	 * Validates a required field. Returns true if and only if the given field is not empty.
	 *
	 * @param string $field
	 * @return bool
	 */
	public function validateRequired( $field ) {
		return !empty( $field );
	}
}
