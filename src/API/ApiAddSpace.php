<?php


namespace WSS\API;

use ApiBase;
use User;
use WSS\NamespaceRepository;
use WSS\Space;
use WSS\Validation\AddSpaceValidationCallback;

class ApiAddSpace extends \ApiBase {
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
     * @throws \ApiUsageException
     * @throws \MWException
     */
    public function execute() {
        $this->validateParameters();

        $request_params = $this->extractRequestParams();

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

        /**
         * @var User
         */
        $current_user = \RequestContext::getMain()->getUser();

        // Add the current user to the list of namespace admins
        $current_user_name = $current_user->getName();
        if ( !in_array( $current_user, $ns_admins ) ) {
            $ns_admins[] = $current_user_name;
        }

        $space = Space::newFromValues( $ns_key, $ns_name, $ns_description, $current_user );
        $namespace_repository = new NamespaceRepository();

        $namespace_id = $namespace_repository->addSpace( $space );

        // Update the list of namespace admins
        $old_space = Space::newFromConstant( $namespace_id );

        $new_space = Space::newFromConstant( $namespace_id );
        $new_space->setSpaceAdministrators( $ns_admins );

        $namespace_repository->updateSpace( $old_space, $new_space );
    }

    /**
     * Evaluates the parameters given to the API module and throws an exception if the
     * validation fails.
     *
     * @throws \ApiUsageException
     */
    private function validateParameters() {
        $request_params = $this->extractRequestParams();

        $ns_key = isset( $request_params["nskey"] ) ? $request_params["nskey"] : false;

        if ( !$ns_key ) {
            $this->dieWithError( wfMessage( "wss-missing-param", "nskey" ) );
        }

        $ns_name = isset( $request_params["nsname"] ) ? $request_params["nsname"] : false;

        if ( !$ns_name ) {
            $this->dieWithError( wfMessage( "wss-missing-param", "nsname" ) );
        }

        $ns_description = isset( $request_params["nsdescription"] ) ? $request_params["nsdescription"] : false;

        if ( !$ns_description ) {
            $this->dieWithError( wfMessage( "wss-missing-param", "nsdescription" ) );
        }

        $ns_admins = isset( $request_params["nsadmins"] ) ? $request_params["nsadmins"] : false;

        if ( !$ns_admins ) {
            $this->dieWithError( wfMessage( "wss-missing-param", "nsadmins" ) );
        }

        // Although this validation is made for HTMLForm, we use it here to avoid repeating ourselves
        $add_space_validation_callback = new AddSpaceValidationCallback();
        $request_data = [
            "namespace" => $ns_key,
            "namespace_name" => $ns_name,
            "description" => $ns_description
        ];

        // Validate "nskey"
        if ( $add_space_validation_callback->validateField( "namespace", $ns_key, $request_data ) !== true ) {
            $this->dieWithError( wfMessage( "wss-invalid-param", "nskey" ) );
        }

        // Validate "nsname"
        if ( $add_space_validation_callback->validateField( "namespace_name", $ns_name, $request_data ) !== true ) {
            $this->dieWithError( wfMessage( "wss-invalid-param", "nsname" ) );
        }

        // Validate "nsdescription"
        if ( $add_space_validation_callback->validateRequired( $ns_description ) !== true ) {
            $this->dieWithError( wfMessage( "wss-invalid-param", "nsdescription" ) );
        }

        // Validate "nsadmins"
        foreach ( $ns_admins as $ns_admin_name ) {
            $user = User::newFromName( $ns_admin_name );

            if ( !$user instanceof User || $user->isAnon() ) {
                $this->dieWithError( wfMessage( "wss-invalid-param", "nsadmins") );
            }
        }
    }

    /**
     * @return array
     */
    public function getAllowedParams() {
        return [
            'nskey' => [
                ApiBase::PARAM_TYPE => "string",
                ApiBase::PARAM_HELP_MSG => "wss-api-nskey-param"
            ],
            'nsname' => [
                ApiBase::PARAM_TYPE => "string",
                ApiBase::PARAM_HELP_MSG => "wss-api-nsname-param"
            ],
            'nsdescription' => [
                ApiBase::PARAM_TYPE => "string",
                ApiBase::PARAM_HELP_MSG => "wss-api-nsdescription-param"
            ],
            'nsadmins' => [
                ApiBase::PARAM_TYPE => "enum",
                ApiBase::PARAM_HELP_MSG => "wss-api-nsadmins-param"
            ]
        ];
    }
}