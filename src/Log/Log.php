<?php

namespace WSS\Log;

use ManualLogEntry;
use MWException;
use RequestContext;
use Title;
use User;

abstract class Log {
	const LOG_TYPE = 'space';

	/**
	 * @var RequestContext
	 */
	private $request_context;

	/**
	 * @var ManualLogEntry
	 */
	private $log_entry;

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var Title
	 */
	private $target;

	/**
	 * @var string
	 */
	private $comment = '';

	/**
	 * Whether or not this log has been published.
	 *
	 * @var bool
	 */
	private $is_published = false;

	/**
	 * @var string
	 */
	private $timestamp;

	/**
	 * @var int
	 */
	private $insert_id;

	/**
	 * Logger constructor.
	 *
	 * @param Title $title
	 * @param string $type
	 * @param string $subtype
	 */
	public function __construct( Title $title, string $subtype ) {
		$this->target = $title;
		$this->request_context = RequestContext::getMain();
		$this->timestamp = wfTimestampNow();
		$this->user = $this->request_context->getUser();
		$this->log_entry = new ManualLogEntry( self::LOG_TYPE, $subtype );
	}

	/**
	 * Set the user for which this log entry should be logged.
	 *
	 * @param User $user
	 */
	final public function setUser( User $user ) {
		$this->user = $user;
	}

	/**
	 * Sets the title for this log.
	 *
	 * @param Title $target
	 */
	final public function setTarget( Title $target ) {
		$this->target = $target;
	}

	/**
	 * Sets the comment for this log.
	 *
	 * @param string $comment
	 */
	final public function setComment( string $comment ) {
		$this->comment = $comment;
	}

	/**
	 * Sets the timestamp for this log.
	 *
	 * @param string $timestamp
	 */
	final public function setTimestamp( string $timestamp ) {
		$this->timestamp = $timestamp;
	}

	/**
	 * Returns the current request context.
	 *
	 * @return RequestContext
	 */
	final public function getRequestContext(): RequestContext {
		return $this->request_context;
	}

	/**
	 * Returns the user for which this log entry should be logged.
	 *
	 * @return User
	 */
	final public function getUser(): User {
		return $this->user;
	}

	/**
	 * Returns the title for this log.
	 *
	 * @return Title
	 */
	final public function getTarget(): Title {
		return $this->target;
	}

	/**
	 * Returns the comment for this log.
	 *
	 * @return string
	 */
	final public function getComment(): string {
		return $this->comment;
	}

	/**
	 * Returns the timestamp for this log.
	 *
	 * @return string
	 */
	final public function getTimestamp(): string {
		return $this->timestamp;
	}

	/**
	 * Returns true if and only if this log is patrollable.
	 *
	 * @see ManualLogEntry::setIsPatrollable()
	 *
	 * @return bool
	 */
	public function isPatrollable(): bool {
		return false;
	}

	/**
	 * Returns the ManualLogEntry object for this log.
	 *
	 * @return ManualLogEntry
	 */
	public function getLogEntry() {
		$this->log_entry->setPerformer( $this->user );
		$this->log_entry->setTarget( $this->target );
		$this->log_entry->setComment( $this->comment );
		$this->log_entry->setParameters( $this->getParameters() );

		if ( $this->isPatrollable() ) {
			$this->log_entry->setIsPatrollable( true );
		}

		$this->log_entry->setTimestamp( $this->getTimestamp() );

		return $this->log_entry;
	}

	/**
	 * Inserts the log into the database.
	 *
	 * To actually publish the log, you must call Log::publish().
	 *
	 * @see Log::publish()
	 *
	 * @throws MWException
	 */
	public function insert() {
		$this->insert_id = $this->getLogEntry()->insert();
	}

	/**
	 * Publishes this log.
	 *
	 * @throws MWException
	 */
	public function publish() {
		if ( $this->is_published === true ) {
			throw new \MWException( "Cannot republish log" );
		}

		if ( !isset( $this->insert_id ) ) {
			$this->insert_id = $this->getLogEntry()->insert();
		}

		$this->getLogEntry()->publish( $this->insert_id );

		$this->is_published = true;
	}

	/**
	 * Returns the parameters to enter into this log.
	 *
	 * @return array
	 */
	abstract public function getParameters(): array;
}
