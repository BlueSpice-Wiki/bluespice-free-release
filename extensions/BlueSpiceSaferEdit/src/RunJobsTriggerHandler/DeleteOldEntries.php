<?php

namespace BlueSpice\SaferEdit\RunJobsTriggerHandler;

use BlueSpice\SaferEdit\KeepAliveWatcher;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\Status\Status;
use MWStake\MediaWiki\Component\RunJobsTrigger\IHandler;
use MWStake\MediaWiki\Component\RunJobsTrigger\Interval;
use Wikimedia\Rdbms\ILoadBalancer;

class DeleteOldEntries implements IHandler {

	public const HANDLER_KEY = 'bs-saferedit-delete-old-entries';

	/**
	 * @param ILoadBalancer $lb
	 */
	public function __construct(
		private readonly ILoadBalancer $lb
	) {
	}

	/**
	 *
	 * @return Interval
	 */
	public function getInterval() {
		return new EveryKeepAliveInterval();
	}

	/**
	 * @return string
	 */
	public function getKey() {
		return self::HANDLER_KEY;
	}

	/**
	 * @return Status
	 */
	public function run() {
		$status = Status::newGood();

		$watcher = new KeepAliveWatcher( $this->lb );
		$watcher->setLogger( LoggerFactory::getInstance( 'BlueSpiceSaferEdit' ) );
		$watcher->removeExpired();

		return $status;
	}
}
