<?php

namespace PDP\Special;

use ErrorPageError;
use PDP\SpecialPage;
use PDP\UI\PermissionsUI;
use PermissionsError;

/**
 * Class SpecialPermissions
 * @package PDP\Special
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
    public function getDescription() {
        return $this->msg( 'pdp-special-title' )->plain();
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
        return 'pdp-manage';
    }

    /**
     * @inheritDoc
     */
    public function getGroupName() {
        return 'users';
    }

    /**
     * @inheritDoc
     * @throws ErrorPageError
     * @throws PermissionsError
     */
    public function preExecuteChecks(): bool {
        $this->checkPermissions();
        return $this->checkLoginSecurityLevel( 'pega-change-permissions' );
    }

    /**
     * @inheritDoc
     */
    public function doExecute( string $parameter ) {
        $handler = new PermissionsUI( $this->getOutput() );

        $handler->setParameter( $parameter );
        $handler->execute();
    }

    /**
     * @inheritDoc
     */
    public function postExecuteCleanup() {
        $this->setHeaders();
    }
}