<?php
namespace WSS\API;

use MediaWiki\MediaWikiServices;
use User;
use WSS\NamespaceRepository;

class ApiQuerySpaceAdmins extends \ApiQueryBase
{

    /**
     * @inheritDoc
     * @throws \ApiUsageException
     */
    public function execute()
    {
        $request_params = $this->extractRequestParams();

        $ns_id = isset( $request_params["namespace"] ) ? $request_params["namespace"] : false;

        if ($ns_id !== false) {
            $currentUser = \RequestContext::getMain()->getUser();
            $usrGrpMng = MediaWikiServices::getInstance()->getUserGroupManager();
            $usrGrps = $usrGrpMng->getUserGroups($currentUser);

            if (!in_array($ns_id."Admin", $usrGrps) && !in_array("sysop", $usrGrps)) {
                $this->dieWithError( wfMessage("wss-permission-denied", "spaceadmins"));
            }

            $namespace_repository = new NamespaceRepository();

            // Get all admin ids for a namespace
            $admin_ids = $namespace_repository->getNamespaceAdmins($ns_id);

            // Turn the list of namespace constants into Space objects
            $admins = array_map( [ User::class, "newFromId" ], $admin_ids );
            $admins = array_filter($admins, function ($user):bool { return !($user instanceof User); });

            // Get a pointer to the result
            $result = $this->getResult();

            $i = 0;
            foreach ($admins as $admin) {
                $result->addValue( $i, "name", $admin->getName() );
                $result->addValue( $i, "user_id", $admin->getId() );
                $i++;
            }
        } else {
            $this->dieWithError( wfMessage( "wss-api-missing-param", "namespace" ) );
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