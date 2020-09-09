<?php


namespace PDP\Special;

use ErrorPageError;
use Exception;
use PDP\NamespaceRepository;
use PDP\SpecialPage;
use PDP\UI\AddSpaceUI;
use PDP\UI\ArchivedSpacesUI;
use PDP\UI\ExceptionUI;
use PDP\UI\InvalidPageUI;
use PDP\UI\UnarchiveSpaceUI;
use PermissionsError;

/**
 * Class SpecialArchivedSpaces
 *
 * @package PDP\Special
 */
class SpecialArchivedSpaces extends SpecialPage {
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
        return "ArchivedSpaces";
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
        return wfMessage( 'pdp-archived-spaces-title' )->plain();
    }

    /**
     * @inheritDoc
     */
    public function getLoginSecurityLevel() {
        return 'pega-manage-namespaces';
    }

    /**
     * @inheritDoc
     * @throws \MWException
     */
    public function doExecute( string $parameter ) {
        try {
            $namespace_repository = new NamespaceRepository();

            if ( !empty( $parameter ) && !in_array( $parameter, $namespace_repository->getArchivedSpaces() ) ) {
                $ui = new InvalidPageUI( $this->getOutput(), $this->getLinkRenderer() );
                $ui->execute();

                return;
            }

            $output   = $this->getOutput();
            $renderer = $this->getLinkRenderer();

            $ui = empty( $parameter ) ?
                new ArchivedSpacesUI( $output, $renderer ) :
                new UnarchiveSpaceUI( $output, $renderer );

            $ui->setParameter( $parameter );
            $ui->execute();
        } catch( Exception $e ) {
            $ui = new ExceptionUI( $e, $this->getOutput(), $this->getLinkRenderer() );
            $ui->execute();
        }
    }
}