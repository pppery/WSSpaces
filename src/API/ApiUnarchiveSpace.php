<?php

namespace WSS\API;

use ApiUsageException;
use WSS\NamespaceRepository;
use WSS\Space;

class ApiUnarchiveSpace extends ApiBase {
	/**
	 * Evaluates the parameters, performs the requested query, and sets up
	 * the result. Concrete implementations of ApiBase must override this
	 * method to provide whatever functionality their module offers.
	 * Implementations must not produce any output on their own and are not
	 * expected to handle any errors.
	 *
	 * The execute() method will be invoked directly by ApiMain immediately
	 * before the result of the module is output. Aside from the
	 * constructor, implementations should assume that no other methods
	 * will be called externally on the module before the result is
	 * processed.
	 *
	 * The result data should be stored in the ApiResult object available
	 * through getResult().
	 *
	 * @throws ApiUsageException
	 * @throws \MWException
	 * @throws \PermissionsError
	 */
	public function execute() {
		if ( !Space::canArchive() ) {
			$this->dieWithError( [ 'apierror-permissiondenied', $this->msg( "action-wss-archive-space" ) ] );
		}

		$request_params = $this->extractRequestParams();

		if ( !isset( $request_params["nsid"] ) ) {
			$this->dieWithError( $this->msg( "wss-api-missing-param-nsid" ) );
		}

		$space = Space::newFromConstant( $request_params["nsid"] );

		if ( !$space instanceof Space ) {
			$this->dieWithError(
				$this->msg(
					"wss-api-invalid-param-detailed-nsid",
					$this->msg( "wss-api-space-does-not-exist", $request_params["nsid"] )->parse()
				)->parse()
			);
		}

		$namespace_repository = new NamespaceRepository();
		$namespace_repository->unarchiveSpace( $space );

		$this->getResult()->addValue( [], "result", "success" );
	}

	/**
	 * @inheritDoc
	 */
	public function getAllowedParams(): array {
		return [
			'nsid' => [
				ApiBase::PARAM_TYPE => "integer",
				ApiBase::PARAM_HELP_MSG => "wss-api-nsid-param"
			]
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getExamplesMessages() {
		return [
			"action=unarchivespace&nsid=50000" =>
				"apihelp-unarchivespace-example-foo"
		];
	}
}
