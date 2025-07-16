<?php

use BlueSpice\ExtendedStatistics\ISnapshotProvider;
use BlueSpice\ExtendedStatistics\Snapshot;
use BlueSpice\ExtendedStatistics\SnapshotDate;
use BlueSpice\ExtendedStatistics\SnapshotDateRange;
use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MediaWikiServices;

require_once dirname( dirname( dirname( __DIR__ ) ) ) . '/maintenance/Maintenance.php';

class GenerateSnapshot extends Maintenance {
	/** @var \BlueSpice\ExtendedStatistics\AttributeRegistryFactory */
	private $providerFactory;
	/** @var \BlueSpice\ExtendedStatistics\ISnapshotStore */
	private $snapshotStore;

	public function __construct() {
		parent::__construct();
		$this->requireExtension( 'BlueSpiceExtendedStatistics' );
		$this->addOption(
			'interval',
			'Interval for which to generate snapshot (day, week, month or year)',
			false
		);
		$this->addOption(
			'regenerate',
			'If set, snapshots will be regenerated if already existing',
			false
		);
		$this->addOption(
			'skip-timecheck',
			'if not set, it will show system time, letting user check if it matches reality'
		);
	}

	public function execute() {
		if ( !$this->hasOption( 'skip-timecheck' ) ) {
			$this->timecheck();
		}
		$this->setServices();
		$start = microtime( true );

		$interval = $this->getOption( 'interval', 'day' );
		if ( $interval === Snapshot::INTERVAL_DAY ) {
			$this->generateForDate( $this->getYesterday() );
		} else {
			$this->aggregate( $interval );
		}

		$end = microtime( true );
		$this->output( "Complete! Took:" . ( round( $end - $start, 2 ) ) . "\n" );
	}

	private function setServices() {
		$services = MediaWikiServices::getInstance();
		$this->providerFactory = $services->getService(
			'ExtendedStatisticsSnapshotProviderFactory'
		);
		$this->snapshotStore = $services->getService( 'ExtendedStatisticsSnapshotStore' );
	}

	/**
	 * @return SnapshotDate
	 */
	private function getYesterday() {
		$date = new SnapshotDate();
		return $date->sub( new DateInterval( 'P1D' ) );
	}

	/**
	 * @param SnapshotDate $date
	 * @throws MWException
	 * @throws ReflectionException
	 */
	private function generateForDate( SnapshotDate $date ) {
		/**
		 * @var string $key
		 * @var ISnapshotProvider $provider
		 */
		foreach ( $this->providerFactory->getAll() as $key => $provider ) {
			$this->output( "Processing provider $key..." );
			if (
				$this->snapshotStore->hasSnapshot( $date, $key ) &&
				!$this->hasOption( 'regenerate' )
			) {
				$this->output( "already exists, skipping\n" );
				continue;
			}

			$snapshot = $provider->generateSnapshot( $date );
			$status = $this->snapshotStore->persistSnapshot( $snapshot );
			$secondaryData = $provider->getSecondaryData( $snapshot );
			if ( is_array( $secondaryData ) ) {
				$this->output( "storing secondary data..." );
				$this->snapshotStore->persistSecondaryData( $snapshot, $secondaryData );
			}

			if ( $status ) {
				$this->output( "done!\n" );
			} else {
				$this->output( "failed!\n" );
			}
		}
	}

	/**
	 * @param string $interval
	 * @throws MWException
	 * @throws ReflectionException
	 */
	private function aggregate( $interval ) {
		$range = null;
		$identifier = null;
		switch ( $interval ) {
			case Snapshot::INTERVAL_WEEK:
				$range = SnapshotDateRange::newLastWeek();
				$identifier = $range->getFrom()->format( 'W' );
				break;
			case Snapshot::INTERVAL_MONTH:
				$range = SnapshotDateRange::newLastMonth();
				$identifier = $range->getFrom()->format( 'F' );
				break;
			case Snapshot::INTERVAL_YEAR:
				$range = SnapshotDateRange::newLastYear();
				$identifier = $range->getFrom()->format( 'Y' );
				break;
		}

		$this->output( "Generating snapshots for $interval $identifier...\n" );
		/**
		 * @var string $key
		 * @var ISnapshotProvider $provider
		 */
		foreach ( $this->providerFactory->getAll() as $key => $provider ) {
			$this->output( "Processing provider $key..." );
			$snapshots = $this->snapshotStore->getSnapshotForRange( $range, $key );
			if ( empty( $snapshots ) ) {
				$this->output( "Found no snapshots for past $interval, skipping\n" );
				continue;
			}
			$aggregated = $provider->aggregate( $snapshots, $interval, $range->getFrom() );
			$status = $this->snapshotStore->persistSnapshot( $aggregated );
			if ( $status ) {
				$this->output( "done!\n" );
			} else {
				$this->output( "failed!\n" );
			}
		}
	}

	private function timecheck() {
		$date = $this->getYesterday();
		$this->output(
			"Yesterday: " . $date->getFloor()->mwTimestamp() . '-' .
			$date->getCeiling()->mwTimestamp() . "\n"
		);
		$this->output(
			"If this time does not match yesterday 00:00:00 to yesterday 23:59:59 abort this...\n"
		);
		$this->countDown( 9 );
	}
}

$maintClass = GenerateSnapshot::class;
require_once RUN_MAINTENANCE_IF_MAIN;
