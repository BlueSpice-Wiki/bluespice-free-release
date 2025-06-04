<?php

namespace BlueSpice\Readers\RunJobsTriggerHandler;

use BlueSpice\RunJobsTriggerHandler;
use MediaWiki\Status\Status;
use MediaWiki\Utils\MWTimestamp;

class DBCleaner extends RunJobsTriggerHandler {

	/**
	 * @var int
	 */
	private $secondsInDay = 86400;

	/**
	 * @return Status
	 */
	protected function doRun() {
		$status = Status::newGood();

		$config = $this->services->getConfigFactory()->makeConfig( 'bsg' );
		if ( $config->get( 'ReadersCleanData' ) !== true ) {
			return $status;
		}

		$daysToLive = (int)$config->get( 'ReadersCleanDataTTL' );

		if ( $daysToLive <= 0 ) {
			return $status;
		}

		$currentTS = MWTimestamp::getInstance( time() - $daysToLive * $this->secondsInDay )
			->getTimestamp( TS_MW );

		$dbr = $this->services->getDBLoadBalancer()->getConnection( DB_REPLICA );

		$entriesCount = $dbr->select(
			'bs_readers',
			[ 'readers_id' ],
			[ 'readers_ts < ' . $currentTS ],
			__METHOD__
		)->numRows();

		if ( $entriesCount < 1 ) {
			return $status;
		}

		$this->services->getDBLoadBalancer()
			->getConnection( DB_PRIMARY )
			->delete(
				'bs_readers',
				[ 'readers_ts < ' . $currentTS ],
				__METHOD__
			);

		wfDebugLog(
			'BlueSpiceReaders',
			$entriesCount . ' old entries has been deleted from bs_readers table'
		);

		return $status;
	}

}
