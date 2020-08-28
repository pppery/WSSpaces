<?php

namespace PDP\Special;

use ErrorPageError;
use Exception;
use PDP\NamespaceRepository;
use PDP\SpecialPage;
use PDP\UI\ExceptionUI;
use PDP\UI\InvalidPageUI;
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
        return 'pdp-spaces';
    }

    /**
     * @inheritDoc
     */
    public function getDescription() {
        return wfMessage( 'pdp-special-permissions-title' )->plain();
    }

    /**
     * @return bool|string
     */
    public function getLoginSecurityLevel() {
        return 'pega-change-permissions';
    }

    /**
     * @inheritDoc
     */
    public function doExecute( string $parameter ) {
        $parameter = $parameter ? $parameter : 'Main';

        try {
            $namespace_repository = new NamespaceRepository();
            $namespaces = $namespace_repository->getNamespaces();

            if (!in_array($parameter, $namespaces)) {
                $ui = new InvalidPageUI( $this->getOutput(), $this->getLinkRenderer() );
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