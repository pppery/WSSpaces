<?php

namespace PDP;

abstract class SpecialPage extends \SpecialPage {
    /**
     * @inheritDoc
     */
    public function getDescription() {
        return $this->msg( 'pdp-special-title' )->plain();
    }

    /**
     * @inheritDoc
     */
    public function execute( $parameter ) {
        $result = $this->preExecuteChecks();

        if ( $result === false ) {
            return false;
        }

        $parameter = $parameter ?? '';

        $this->doExecute( $parameter );
        $this->postExecuteCleanup();

        return true;
    }

    /**
     * Does some checks before the special page is even shown. Return false to abort the execution of the
     * special page.
     *
     * @return bool
     */
    abstract function preExecuteChecks(): bool;

    /**
     * The main method that gets invoked upon successfully loading the special page, after preExecuteChecks have
     * been performed.
     *
     * @param $parameter
     * @return void
     */
    abstract function doExecute( string $parameter );

    /**
     * Cleans up the special page after the main execute method. Can for instance be used to set headers or commit
     * some database transactions.
     *
     * @return void
     */
    abstract function postExecuteCleanup();
}