<?php


namespace WSS\API;

use ApiUsageException;
use MWException;
use User;
use WSS\NamespaceRepository;
use WSS\Space;
use WSS\Validation\AddSpaceValidationCallback;

class ApiEditSpace extends ApiBase {
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
     * @throws MWException
     */
    public function execute() {
        $this->validateParameters();

        $request_params = $this->extractRequestParams();

        /**
         * @var int
         */
        $ns_id = $request_params["nsid"];

        /**
         * @var string
         */
        $ns_key = $request_params["nskey"];

        /**
         * @var string
         */
        $ns_name = $request_params["nsname"];

        /**
         * @var string
         */
        $ns_description = $request_params["nsdescription"];

        /**
         * @var array
         */
        $ns_admins = $request_params["nsadmins"];
        $ns_admins = explode( ",", $ns_admins );

        $namespace_repository = new NamespaceRepository();

        $old_space = Space::newFromConstant( $ns_id );
        $new_space = clone $old_space;

        $new_space->setKey( $ns_key );
        $new_space->setDescription( $ns_description );
        $new_space->setName( $ns_name );
        $new_space->setSpaceAdministrators( $ns_admins );

        try {
            $namespace_repository->updateSpace( $old_space, $new_space );
        } catch( \PermissionsError $e ) {
            // The user is not allowed to update this space
            $this->dieWithError( [ 'apierror-permissiondenied', $this->msg( "action-wss-edit-space" ) ] );
        }

        $this->getResult()->addValue( [], "result", "success" );
    }

    /**
     * Evaluates the parameters given to the API module and throws an exception if the
     * validation fails.
     *
     * @throws ApiUsageException
     */
    private function validateParameters() {
        $request_params = $this->extractRequestParams();

        $ns_id = isset( $request_params["nsid"] ) ? $request_params["nsid"] : false;

        if ( $ns_id === false ) {
            $this->dieWithError( wfMessage( "wss-api-missing-param", "nsid" ) );
        }

        $ns_key = isset( $request_params["nskey"] ) ? $request_params["nskey"] : false;

        if ( $ns_key === false ) {
            $this->dieWithError( wfMessage( "wss-api-missing-param", "nskey" ) );
        }

        $ns_name = isset( $request_params["nsname"] ) ? $request_params["nsname"] : false;

        if ( $ns_name === false ) {
            $this->dieWithError( wfMessage( "wss-api-missing-param", "nsname" ) );
        }

        $ns_description = isset( $request_params["nsdescription"] ) ? $request_params["nsdescription"] : false;

        if ( $ns_description === false ) {
            $this->dieWithError( wfMessage( "wss-api-missing-param", "nsdescription" ) );
        }

        $ns_admins = isset( $request_params["nsadmins"] ) ? $request_params["nsadmins"] : false;

        if ( $ns_admins === false ) {
            $this->dieWithError( wfMessage( "wss-api-missing-param", "nsadmins" ) );
        }

        $space = Space::newFromConstant( $ns_id );

        if ( !$space instanceof Space ) {
            $this->dieWithError(
                wfMessage(
                    "wss-api-invalid-param-detailed",
                    "nsid",
                    wfMessage( "wss-api-space-does-not-exist", $request_params["nsid"] )->parse()
                )
            );
        }

        if ( !$space->canEdit() ) {
            $this->dieWithError( [ 'apierror-permissiondenied', $this->msg( "action-wss-edit-space" ) ] );
        }

        // Although this validation is made for HTMLForm, we use it here to avoid repeating ourselves
        $add_space_validation_callback = new AddSpaceValidationCallback();
        $request_data = [
            "namespaceid" => $ns_id,
            "namespace" => $ns_key,
            "namespace_name" => $ns_name,
            "description" => $ns_description
        ];

        // Validate "nskey"
        if ( $add_space_validation_callback->validateField( "namespace", $ns_key, $request_data ) !== true ) {
            $this->dieWithError( wfMessage( "wss-api-invalid-param", "nskey" ) );
        }

        // Validate "nsname"
        if ( $add_space_validation_callback->validateField( "namespace_name", $ns_name, $request_data ) !== true ) {
            $this->dieWithError( wfMessage( "wss-api-invalid-param", "nsname" ) );
        }

        // Validate "nsdescription"
        if ( $add_space_validation_callback->validateRequired( $ns_description ) !== true ) {
            $this->dieWithError( wfMessage( "wss-api-invalid-param", "nsdescription" ) );
        }

        $ns_admins = explode( ",", $ns_admins );

        // Validate "nsadmins"
        foreach ( $ns_admins as $ns_admin_name ) {
            $user = User::newFromName( $ns_admin_name );

            if ( !$user instanceof User || $user->isAnon() ) {
                $this->dieWithError( wfMessage( "wss-api-invalid-param", "nsadmins") );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getAllowedParams(): array {
        return [
            'nsid' => [
                ApiBase::PARAM_TYPE => "integer",
                ApiBase::PARAM_HELP_MSG => "wss-api-nsid-param",
                ApiBase::PARAM_REQUIRED => true
            ],
            'nskey' => [
                ApiBase::PARAM_TYPE => "string",
                ApiBase::PARAM_HELP_MSG => "wss-api-nskey-param",
                ApiBase::PARAM_REQUIRED => true
            ],
            'nsname' => [
                ApiBase::PARAM_TYPE => "string",
                ApiBase::PARAM_HELP_MSG => "wss-api-nsname-param",
                ApiBase::PARAM_REQUIRED => true
            ],
            'nsdescription' => [
                ApiBase::PARAM_TYPE => "string",
                ApiBase::PARAM_HELP_MSG => "wss-api-nsdescription-param",
                ApiBase::PARAM_REQUIRED => true
            ],
            'nsadmins' => [
                ApiBase::PARAM_TYPE => "string",
                ApiBase::PARAM_HELP_MSG => "wss-api-nsadmins-param",
                ApiBase::PARAM_REQUIRED => true
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function getExamplesMessages() {
        return [
            "action=editspace&nsid=50000&nskey=Foo&nsname=Foo&nsdescription=Lorem ipsum&nsadmins=Admin,Foobar" =>
                "apihelp-addspace-example-50000"
        ];
    }
}