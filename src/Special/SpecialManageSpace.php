<?php


namespace WSS\Special;

use Exception;
use WSS\NamespaceRepository;
use WSS\Space;
use WSS\SpecialPage;
use WSS\UI\ExceptionUI;
use WSS\UI\InvalidPageUI;
use WSS\UI\ManageSpaceBaseUI;
use WSS\UI\ManageSpaceFormUI;
use WSS\UI\MissingPermissionsUI;

/**
 * Class SpecialManageSpace
 *
 * @package WSS\Special
 */
class SpecialManageSpace extends SpecialPage {
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
        return "ManageSpace";
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
        return wfMessage( 'wss-manage-space-title' )->plain();
    }

    /**
     * @inheritDoc
     */
    public function getLoginSecurityLevel() {
        return 'ws-manage-namespaces';
    }

    /**
     * The main method that gets invoked upon successfully loading the special page, after preExecuteChecks have
     * been performed.
     *
     * @param $parameter
     * @return void
     * @throws \MWException
     */
    function doExecute( string $parameter ) {
        $output = $this->getOutput();
        $renderer = $this->getLinkRenderer();

        if ( empty( $parameter ) ) {
            $ui = new ManageSpaceBaseUI( $output, $renderer );
            $ui->execute();

            return;
        }

        try {
            $namespace_repository = new NamespaceRepository();

            if ( !in_array( $parameter, $namespace_repository->getSpaces( true ) ) ) {
                $ui = new InvalidPageUI( $this->getOutput(), $this->getLinkRenderer() );
                $ui->execute();

                return;
            }

            $space = Space::newFromConstant( $parameter );
            if ( !$space->canEdit() ) {
                $ui = new MissingPermissionsUI( $this->getOutput(), $this->getLinkRenderer() );
                $ui->execute();

                return;
            }

            $ui = new ManageSpaceFormUI( $output, $renderer );
            $ui->setParameter( $parameter );
            $ui->execute();
        } catch( Exception $e ) {
            $ui = new ExceptionUI( $e, $this->getOutput(), $this->getLinkRenderer() );
            $ui->execute();
        }
    }
}