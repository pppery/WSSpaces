<?php


namespace PDP\Special;

use Exception;
use PDP\NamespaceRepository;
use PDP\Space;
use PDP\SpecialPage;
use PDP\UI\ExceptionUI;
use PDP\UI\InvalidPageUI;
use PDP\UI\ManageSpaceBaseUI;
use PDP\UI\ManageSpaceFormUI;
use PDP\UI\MissingPermissionsUI;

/**
 * Class SpecialManageSpace
 *
 * @package PDP\Special
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
        return wfMessage( 'pdp-manage-space-title' )->plain();
    }

    /**
     * @inheritDoc
     */
    public function getLoginSecurityLevel() {
        return 'pega-manage-namespaces';
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
        try {
            $namespace_repository = new NamespaceRepository();

            if ( !empty( $parameter ) ) {
                if ( !in_array( $parameter, $namespace_repository->getSpaces() ) ) {
                    $ui = new InvalidPageUI( $this->getOutput(), $this->getLinkRenderer() );
                    $ui->execute();

                    return;
                }

                $space = Space::newFromName( $parameter );
                if ( !$space->canEdit() ) {
                    $ui = new MissingPermissionsUI( $this->getOutput(), $this->getLinkRenderer() );
                    $ui->execute();

                    return;
                }
            }

            $output = $this->getOutput();
            $renderer = $this->getLinkRenderer();

            $ui = empty( $parameter ) ?
                new ManageSpaceBaseUI( $output, $renderer ) :
                new ManageSpaceFormUI( $output, $renderer );

            $ui->setParameter( $parameter );
            $ui->execute();
        } catch( Exception $e ) {
            $ui = new ExceptionUI( $e, $this->getOutput(), $this->getLinkRenderer() );
            $ui->execute();
        }
    }
}