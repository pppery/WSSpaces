<?php


namespace WSS\Special;

use ErrorPageError;
use Exception;
use WSS\NamespaceRepository;
use WSS\Space;
use WSS\SpecialPage;
use WSS\UI\AddSpaceUI;
use WSS\UI\ArchivedSpacesUI;
use WSS\UI\ExceptionUI;
use WSS\UI\InvalidPageUI;
use WSS\UI\MissingPermissionsUI;
use WSS\UI\UnarchiveSpaceUI;
use PermissionsError;

/**
 * Class SpecialArchivedSpaces
 *
 * @package WSS\Special
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
        return wfMessage( 'wss-archived-spaces-title' )->plain();
    }

    /**
     * @inheritDoc
     */
    public function getLoginSecurityLevel() {
        return 'ws-manage-namespaces';
    }

    /**
     * @inheritDoc
     * @throws \MWException
     */
    public function doExecute( string $parameter ) {
        try {
            $namespace_repository = new NamespaceRepository();

            if ( !empty( $parameter ) ) {
                if ( !in_array( $parameter, $namespace_repository->getArchivedSpaces() ) ) {
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