<?php


namespace PDP\UI;

use MediaWiki\Linker\LinkRenderer;
use OutputPage;
use Xml;

class ExceptionUI extends PDPUI {
    /**
     * @var \Exception
     */
    private $exception;

    /**
     * ExceptionUI constructor.
     *
     * @param \Exception $exception
     * @param OutputPage $page
     * @param LinkRenderer $link_renderer
     * @throws \MWException
     */
    public function __construct( \Exception $exception, OutputPage $page, LinkRenderer $link_renderer ) {
        $this->exception = $exception;

        parent::__construct($page, $link_renderer);
    }

    /**
     * @inheritDoc
     */
    function render() {
        $this->getOutput()->addWikiMsg( 'pdp-internal-exception-intro' );
        $this->getOutput()->addHTML( Xml::tags( 'h1', [], wfMessage( 'pdp-debug-information' ) ) );
        $this->getOutput()->addWikiMsg( 'pdp-debug-information-intro' );

        $debug_information = $this->exception->getMessage() .
            Xml::tags( 'br', [], '' ) .
            nl2br( $this->exception->getTraceAsString() );

        $this->getOutput()->addHTML(
            Xml::tags( 'div', [ 'class' => 'pdp-exception-notice' ], $debug_information )
        );

        $this->getOutput()->addHTML( Xml::tags( 'h1', [], wfMessage( 'pdp-how-to-get-help' ) ) );
        $this->getOutput()->addWikiMsg( 'pdp-how-to-get-help-intro' );
    }

    /**
     * @inheritDoc
     */
    function getIdentifier(): string {
        return 'internal-exception';
    }

    /**
     * @inheritDoc
     */
    public function getHeaderPrefix(): string {
        return "\u{1f6ab}";
    }

    /**
     * @inheritDoc
     */
    public function getNavigationPrefix(): string {
        return wfMessage('pdp-invalidpage-topnav')->plain();
    }

    /**
     * @inheritDoc
     */
    public function getNavigationItems(): array {
        return [
            wfMessage( 'pdp-special-permissions-title' )->plain() => 'Special:Permissions',
            wfMessage( 'pdp-add-space-header' )->plain() => 'Special:AddSpace',
            wfMessage( 'pdp-manage-space-header' )->plain() => 'Special:ManageSpace',
            wfMessage( 'pdp-archived-spaces-header' )->plain() => 'Special:ArchivedSpaces'
        ];
    }

    /**
     * @inheritDoc
     */
    public function getModules(): array {
        return [ 'ext.pdp.Exception' ];
    }
}