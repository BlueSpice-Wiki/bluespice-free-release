<?php

namespace BlueSpice\ExtendedStatistics\SnapshotStore;

use BlueSpice\ExtendedStatistics\ISnapshotStore;
use BlueSpice\ExtendedStatistics\Snapshot;
use BlueSpice\ExtendedStatistics\SnapshotDate;
use BlueSpice\ExtendedStatistics\SnapshotDateRange;
use BlueSpice\ExtendedStatistics\SnapshotFactory;
use DateInterval;
use Exception;
use Wikimedia\Rdbms\LoadBalancer;

class DatabaseStore implements ISnapshotStore {
	public const TABLE = 'bs_extendedstatistics_snapshot';
	public const FIELDS = [ 'ess_type', 'ess_data', 'ess_timestamp', 'ess_interval' ];

	/** @var LoadBalancer */
	private $loadBalancer;
	/** @var array */
	private $conds = [];

	/** @var SnapshotFactory */
	private SnapshotFactory $snapshotFactory;

	/**
	 * @param LoadBalancer $loadBalancer
	 * @param SnapshotFactory $snapshotFactory
	 */
	public function __construct( LoadBalancer $loadBalancer, SnapshotFactory $snapshotFactory ) {
		$this->loadBalancer = $loadBalancer;
		$this->snapshotFactory = $snapshotFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function getSnapshotForRange(
		SnapshotDateRange $range, $type = null, $interval = 'day'
	): array {
		return $this->forRange( $range )->forType( $type )->forInterval( $interval )->query();
	}

	/**
	 * @param SnapshotDate $date
	 * @param string $type
	 * @param string|null $interval
	 * @return Snapshot|mixed|null
	 * @throws Exception
	 */
	public function getPrevious(
		SnapshotDate $date, $type, $interval = Snapshot::INTERVAL_DAY
	) {
		$shortInterval = 'D';
		switch ( $interval ) {
			case Snapshot::INTERVAL_WEEK:
				$shortInterval = 'W';
				break;
			case Snapshot::INTERVAL_MONTH:
				$shortInterval = 'M';
				break;
			case Snapshot::INTERVAL_YEAR:
				$shortInterval = 'Y';
				break;
		}
		$date = $date->sub( new DateInterval( "P1$shortInterval" ) );
		$range = SnapshotDateRange::newSingleDay( $date );
		$snapshots = $this->forRange( $range )->forType( $type )->forInterval( $interval )->query();
		if ( empty( $snapshots ) ) {
			return null;
		}
		return $snapshots[0];
	}

	/**
	 * @param SnapshotDate $date
	 * @param string $type
	 * @param string $interval
	 * @return bool
	 * @throws Exception
	 */
	public function hasSnapshot( SnapshotDate $date, $type, $interval = 'day' ) {
		$snapshots = $this->forRange( SnapshotDateRange::newSingleDay( $date ) )
			->forType( $type )->forInterval( $interval )->query();
		return !empty( $snapshots );
	}

	/**
	 * @inheritDoc
	 */
	public function persistSnapshot( Snapshot $snapshot ) {
		$db = $this->loadBalancer->getConnection( DB_PRIMARY );
		if (
			$this->hasSnapshot(
				$snapshot->getDate(), $snapshot->getType(), $snapshot->getInterval()
			)
		) {
			return $db->update(
				static::TABLE,
				[
					'ess_data' => json_encode( $snapshot->getData() ),
				],
				[
					'ess_type' => $snapshot->getType(),
					'ess_timestamp' => $db->timestamp( $snapshot->getDate()->mwTimestamp() ),
					'ess_interval' => $snapshot->getInterval(),
				],
				__METHOD__
			);
		}
		return $db->insert(
			static::TABLE,
			[
				'ess_type' => $snapshot->getType(),
				'ess_timestamp' => $db->timestamp( $snapshot->getDate()->mwTimestamp() ),
				'ess_data' => json_encode( $snapshot->getData() ),
				'ess_interval' => $snapshot->getInterval()
			],
			__METHOD__
		);
	}

	/**
	 * @inheritDoc
	 */
	public function persistSecondaryData( Snapshot $snapshot, $data ) {
		$db = $this->loadBalancer->getConnection( DB_PRIMARY );

		return $db->update(
			static::TABLE,
			[
				'ess_secondary_data' => json_encode( $data ),
			],
			[
				'ess_type' => $snapshot->getType(),
				'ess_interval' => $snapshot->getInterval(),
				'ess_timestamp' => $db->timestamp( $snapshot->getDate()->mwTimestamp() ),
			],
			__METHOD__
		);
	}

	/**
	 * @param string $type
	 * @return static
	 */
	private function forType( $type ) {
		$db = $this->loadBalancer->getConnection( DB_REPLICA );
		$this->conds[] = "ess_type = " . $db->addQuotes( $type );
		return $this;
	}

	/**
	 * @param SnapshotDateRange $range
	 * @return static
	 */
	private function forRange( SnapshotDateRange $range ) {
		$this->dateCondition(
			$range->getFrom(),
			$range->getTo()
		);

		return $this;
	}

	/**
	 * @param string $interval
	 * @return static
	 */
	private function forInterval( $interval ) {
		$db = $this->loadBalancer->getConnection( DB_REPLICA );
		$this->conds[] = "ess_interval = " . $db->addQuotes( $interval );

		return $this;
	}

	/**
	 * @param SnapshotDate $from
	 * @param SnapshotDate|null $to
	 */
	private function dateCondition( SnapshotDate $from, $to = null ) {
		$db = $this->loadBalancer->getConnection( DB_REPLICA );

		if ( $to === null ) {
			$this->conds[] = "ess_timestamp = {$db->timestamp( $from->mwTimestamp() )}";
		} else {
			$this->conds[] = implode( ' AND ', [
				"ess_timestamp >= {$db->timestamp( $from->mwTimestamp() )}",
				"ess_timestamp <= {$db->timestamp( $to->mwTimestamp() )}",
			] );
		}
	}

	/**
	 * Execute snapshot query
	 *
	 * @return array
	 * @throws Exception
	 */
	private function query() {
		$db = $this->loadBalancer->getConnection( DB_REPLICA );
		$res = $db->select(
			static::TABLE,
			static::FIELDS,
			$this->conds,
			__METHOD__,
			[
				'ORDER BY' => 'ess_timestamp ASC'
			]
		);

		// reset conds
		$this->conds = [];
		$snapshots = [];
		foreach ( $res as $row ) {
			$snapshots[] = $this->snapshotFromRow( $row );
		}

		return array_filter( $snapshots, static function ( $snapshot ) {
			return $snapshot !== null;
		} );
	}

	/**
	 * @param \stdClass $row
	 * @return Snapshot
	 * @throws Exception
	 */
	private function snapshotFromRow( $row ): Snapshot {
		$date = SnapshotDate::newFromMWTimestamp( $row->ess_timestamp );
		$data = json_decode( $row->ess_data, true );
		$type = $row->ess_type;
		$interval = $row->ess_interval;

		return $this->snapshotFactory->createSnapshot( $date, $type, $data, $interval );
	}
}
