<?php

namespace WSS\UI;

use MediaWiki\Linker\LinkRenderer;
use OutputPage;
use Xml;

class ExceptionUI extends WSSUI {
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

		parent::__construct( $page, $link_renderer );
	}

	/**
	 * @inheritDoc
	 */
	public function render() {
		$this->getOutput()->addWikiMsg( 'wss-internal-exception-intro' );
		$this->getOutput()->addHTML( Xml::tags( 'h1', [], wfMessage( 'wss-debug-information' ) ) );
		$this->getOutput()->addWikiMsg( 'wss-debug-information-intro' );

		$debug_information = $this->exception->getMessage() .
			Xml::tags( 'br', [], '' ) .
			nl2br( $this->exception->getTraceAsString() );

		$this->getOutput()->addHTML(
			Xml::tags( 'div', [ 'class' => 'wss-exception-notice' ], $debug_information )
		);

		$this->getOutput()->addHTML( Xml::tags( 'h1', [], wfMessage( 'wss-how-to-get-help' ) ) );
		$this->getOutput()->addWikiMsg( 'wss-how-to-get-help-intro' );
	}

	/**
	 * @inheritDoc
	 */
	public function getIdentifier(): string {
		return 'internal-exception';
	}

	/**
	 * @inheritDoc
	 */
	public function getHeaderPrefix(): string {
		return "\u{1F6AB}";
	}

	/**
	 * @inheritDoc
	 */
	public function getNavigationPrefix(): string {
		return wfMessage( 'wss-invalidpage-topnav' )->plain();
	}

	/**
	 * @inheritDoc
	 */
	public function getModules(): array {
		return [ 'ext.wss.Exception' ];
	}
}
