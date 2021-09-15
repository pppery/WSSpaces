<?php

namespace WSS\API;

use ApiUsageException;
use MediaWiki\MediaWikiServices;

abstract class ApiBase extends \ApiBase {
	/**
	 * @param array|\Message|string $msg
	 * @param null $code
	 * @param null $data
	 * @param null $httpCode
	 * @throws ApiUsageException
	 */
	public function dieWithError( $msg, $code = null, $data = null, $httpCode = null ) {
		$exception = ApiUsageException::newWithMessage( $this, $msg, $code, $data, $httpCode );

		MediaWikiServices::getInstance()->getHookContainer()->run(
			"WSSpacesCustomApiExceptionHandler",
			[ $exception ]
		);

		throw $exception;
	}

	/**
	 * @inheritDoc
	 */
	public function needsToken() {
		return 'csrf';
	}
}
