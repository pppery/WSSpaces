<?php


namespace WSS\API;

use ApiUsageException;

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

        \Hooks::run( "WSSpacesCustomApiExceptionHandler", [ $exception ] );

        throw $exception;
    }
}