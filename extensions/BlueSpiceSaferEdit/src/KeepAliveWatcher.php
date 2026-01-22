<?php

namespace BlueSpice\SaferEdit;

use MediaWiki\Title\Title;
use MediaWiki\User\User;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * Dead-simple fallback option to remove editing sessions that failed to close/keep-alive using normal mechanisms
 */
class KeepAliveWatcher implements LoggerAwareInterface {

	/** @var int After this many seconds of inactivity, assume user is no longer editing */
	public const SAFER_EDIT_KEEP_ALIVE_INTERVAL = 300;

	/** @var \Psr\Log\LoggerInterface */
	private $logger;

	/**
	 * @param ILoadBalancer $lb
	 */
	public function __construct(
		private readonly ILoadBalancer $lb

	) {
		$this->logger = new NullLogger();
	}

	/**
	 * @param Title $title
	 * @param User $user
	 * @return void
	 */
	public function updateTimestamp( Title $title, User $user ) {
		$db = $this->lb->getConnection( DB_PRIMARY );
		$db->newUpdateQueryBuilder()
			->update( 'bs_saferedit' )
			->set( [
				'se_timestamp' => $db->timestamp(),
			] )
			->where( [
				"se_user_name" => $user->getName(),
				"se_page_title" => $title->getDBkey(),
				"se_page_namespace" => $title->getNamespace(),
			] )
			->caller( __METHOD__ )
			->execute();
	}

	/**
	 * @return void
	 */
	public function removeExpired() {
		$time = wfTimestamp( TS_MW, time() - self::SAFER_EDIT_KEEP_ALIVE_INTERVAL );
		$this->logger->debug( 'Cleaning up expired entries, older than {time}', [
			'time' => $time,
		] );
		$db = $this->lb->getConnection( DB_PRIMARY );
		$db->newDeleteQueryBuilder()
			->deleteFrom( 'bs_saferedit' )
			->where( [
				'se_timestamp < ' . $db->addQuotes( $time ),
			] )
			->caller( __METHOD__ )
			->execute();
	}

	/**
	 * @param LoggerInterface $logger
	 * @return void
	 */
	public function setLogger( LoggerInterface $logger ): void {
		$this->logger = $logger;
	}
}
