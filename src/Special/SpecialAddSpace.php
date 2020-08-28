<?php

namespace PDP\Special;

use ErrorPageError;
use Exception;
use PDP\SpecialPage;
use PDP\UI\AddSpaceUI;
use PDP\UI\ExceptionUI;
use PDP\UI\InvalidPageUI;
use PermissionsError;

/**
 * Class SpecialNamespaces
 * @package PDP\Special
 */
class SpecialAddSpace extends SpecialPage {
    /**
     * SpecialPermissions constructor.
     *
     * @throws \UserNotLoggedIn
     */
    public function __construct() {
        parent::__construct( self::getName(), self::getRestriction(), true );
        parent::requireLogin();
    }

    /**
     * @inheritDoc
     */
    public function getName() {
        return "AddSpace";
    }

    /**
     * @inheritDoc
     */
    public function getRestriction() {
        return 'pdp-manage';
    }

    /**
     * @inheritDoc
     */
    public function getGroupName() {
        return 'pdp-spaces';
    }

    /**
     * @inheritDoc
     */
    public function getDescription() {
        return wfMessage( 'pdp-add-space-header' )->plain();
    }

    /**
     * @inheritDoc
     */
    public function getLoginSecurityLevel() {
        return 'pega-create-namespaces';
    }

    /**
     * @inheritDoc
     */
    public function doExecute( string $parameter ) {
        try {
            if ( !empty( $parameter ) ) {
                $ui = new InvalidPageUI( $this->getOutput(), $this->getLinkRenderer() );
                $ui->execute();

                return;
            }

            $ui = new AddSpaceUI( $this->getOutput(), $this->getLinkRenderer() );
            $ui->execute();
        } catch( Exception $e ) {
            $ui = new ExceptionUI( $e, $this->getOutput(), $this->getLinkRenderer() );
            $ui->execute();
        }

    }
}