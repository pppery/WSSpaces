<?php

namespace PDP\Special;

use ErrorPageError;
use PDP\SpecialPage;
use PDP\UI\InvalidPageUI;
use PDP\UI\PermissionsUI;
use PDP\Validation\PermissionsMatrixValidationCallback;
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
    public function preExecute(): bool {
        $this->setHeaders();
        $this->checkPermissions();

        return $this->checkLoginSecurityLevel( 'pega-change-permissions' );
    }

    /**
     * @inheritDoc
     * @throws \ConfigException
     */
    public function doExecute( string $parameter ) {
        $parameter = $parameter ? $parameter : 'Main';

        if (!in_array($parameter, PermissionsMatrixValidationCallback::getValidSpaces())) {
            $ui = new InvalidPageUI( $this->getOutput(), $this->getLinkRenderer() );
            $ui->execute();

            return;
        }

        $ui = new PermissionsUI( $this->getOutput(), $this->getLinkRenderer() );

        $ui->setParameter( $parameter );
        $ui->execute();
    }

    /**
     * @inheritDoc
     */
    public function postExecuteCleanup() {

    }
}