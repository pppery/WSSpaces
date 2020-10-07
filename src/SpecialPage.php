<?php

namespace WSS;

use ErrorPageError;
use PermissionsError;

abstract class SpecialPage extends \SpecialPage {
    /**
     * @inheritDoc
     * @throws PermissionsError
     * @throws ErrorPageError
     */
    public function execute( $parameter ) {
        $this->setHeaders();
        $this->checkPermissions();

        $security_level = $this->getLoginSecurityLevel();

        if ( $security_level ) {
            $proceed = $this->checkLoginSecurityLevel( $security_level );

            if ( !$proceed ) {
                return;
            }
        }

        $this->preExecute();

        $parameter = $parameter ?? '';

        $this->doExecute( $parameter );
        $this->postExecuteCleanup();
    }

    /**
     * Cleans up the special page after the main execute method. Can for instance be used to set headers or commit
     * some database transactions.
     *
     * @return void
     */
    public function postExecuteCleanup() {
    }

    /**
     * Does some checks before the special page is even shown. Return false to abort the execution of the
     * special page.
     *
     * @return void
     */
    public function preExecute() {
    }

    /**
     * The main method that gets invoked upon successfully loading the special page, after preExecuteChecks have
     * been performed.
     *
     * @param $parameter
     * @return void
     */
    abstract function doExecute( string $parameter );
}