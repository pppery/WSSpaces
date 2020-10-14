<?php

namespace WSS\Special;

use ErrorPageError;
use Exception;
use WSS\NamespaceRepository;
use WSS\Space;
use WSS\SpecialPage;
use WSS\UI\ExceptionUI;
use WSS\UI\InvalidPageUI;
use WSS\UI\MissingPermissionsUI;
use WSS\UI\PermissionsUI;
use PermissionsError;

/**
 * Class SpecialPermissions
 * @package WSS\Special
 */
class SpecialPermissions extends SpecialPage {
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
        return "Permissions";
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
        return wfMessage( 'wss-special-permissions-title' )->plain();
    }

    /**
     * @return bool|string
     */
    public function getLoginSecurityLevel() {
        return 'ws-change-permissions';
    }

    /**
     * @inheritDoc
     * @throws \MWException
     */
    public function doExecute( string $parameter ) {
        try {
            $namespace_repository = new NamespaceRepository();

            if ( !in_array( $parameter, $namespace_repository->getSpaces( true ) ) ) {
                $ui = new InvalidPageUI( $this->getOutput(), $this->getLinkRenderer() );
                $ui->execute();

                return;
            }

            $space = Space::newFromConstant( $parameter );
            if ( $space && !$space->canEdit() ) {
                $ui = new MissingPermissionsUI( $this->getOutput(), $this->getLinkRenderer() );
                $ui->execute();

                return;
            }

            $ui = new PermissionsUI( $this->getOutput(), $this->getLinkRenderer() );
            $ui->setParameter( $parameter );

            $ui->execute();
        } catch( Exception $e ) {
            $ui = new ExceptionUI( $e, $this->getOutput(), $this->getLinkRenderer() );
            $ui->execute();
        }
    }
}