<?php
namespace WSS\API;

use MediaWiki\MediaWikiServices;
use User;
use WSS\NamespaceRepository;

class ApiQuerySpaceAdmins extends \ApiQueryBase {

	/**
	 * @inheritDoc
	 * @throws \ApiUsageException
	 */
	public function execute() {
		$request_params = $this->extractRequestParams();

		$space_id = isset( $request_params["namespace"] ) ? $request_params["namespace"] : false;

		if ( $space_id === false ) {
			$this->dieWithError( wfMessage( "wss-api-missing-param-namespace" ) );
		}

		$user = \RequestContext::getMain()->getUser();
		$group_manager = MediaWikiServices::getInstance()->getUserGroupManager();
		$user_groups = $group_manager->getUserGroups( $user );

		if ( !in_array( $space_id . "Admin", $user_groups ) && !in_array( "sysop", $user_groups ) ) {
			$this->dieWithError( wfMessage( "wss-permission-denied-spaceadmins" ) );
		}

		$namespace_repository = new NamespaceRepository();

		// Get all admin ids for a namespace
		$admin_ids = $namespace_repository->getNamespaceAdmins( $space_id );

		// Turn the list of namespace constants into Space objects
		$admins = array_map( [ User::class, "newFromId" ], $admin_ids );
		$admins = array_filter( $admins, function ( $user ): bool {
			return ( $user instanceof User );
		} );

		// Get a pointer to the result
		$result = $this->getResult();

		foreach ( $admins as $key => $admin ) {
			$result->addValue( [ "admins", $key ], "admin_id", (int)$admin->getId() );
			$result->addValue( [ "admins", $key ], "admin_name", $admin->getName() );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getAllowedParams(): array {
		return [
			'namespace' => [
				ApiBase::PARAM_TYPE => "integer",
				ApiBase::PARAM_HELP_MSG => "wss-api-namespace-param"
			]
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getExamplesMessages() {
		return [
			"action=query&list=namespaceadmins&namespace=50000" =>
				"apihelp-query-example-namespace-admins"
		];
	}
}
