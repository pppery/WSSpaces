<?php

namespace WSS\Special;

use ErrorPageError;
use Exception;
use WSS\SpecialPage;
use WSS\UI\AddSpaceUI;
use WSS\UI\ExceptionUI;
use WSS\UI\InvalidPageUI;
use PermissionsError;

/**
 * Class SpecialNamespaces
 * @package WSS\Special
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
        return 'wss-manage';
    }

    /**
     * @inheritDoc
     */
    public function getGroupName() {
        return 'wss-spaces';
    }

    /**
     * @inheritDoc
     */
    public function getDescription() {
        return wfMessage( 'wss-add-space-header' )->plain();
    }

    /**
     * @inheritDoc
     */
    public function getLoginSecurityLevel() {
        return 'ws-create-namespaces';
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