<?php

namespace BlueSpice\WhoIsOnline\Statistics\SnapshotProvider;

use BlueSpice\ExtendedStatistics\ISnapshotProvider;
use BlueSpice\ExtendedStatistics\Snapshot;
use BlueSpice\ExtendedStatistics\SnapshotDate;
use Wikimedia\Rdbms\LoadBalancer;

class LoginCount implements ISnapshotProvider {
	/** @var LoadBalancer */
	private $loadBalancer;

	/**
	 * @param LoadBalancer $loadBalancer
	 */
	public function __construct( LoadBalancer $loadBalancer ) {
		$this->loadBalancer = $loadBalancer;
	}

	/**
	 * @param SnapshotDate $date
	 * @return Snapshot
	 */
	public function generateSnapshot( SnapshotDate $date ): Snapshot {
		$db = $this->loadBalancer->getConnection( DB_REPLICA );

		$ts = $date->mwDate();
		$res = $db->select(
			'bs_whoisonline',
			[ 'wo_user_name as user', 'wo_log_ts as ping' ],
			[
				"wo_log_ts LIKE " . $db->addQuotes( "$ts%" ),
			],
			__METHOD__,
			[
				'ORDER BY' => 'wo_user_name, wo_log_ts'
			]
		);

		$users = [];
		$lastUser = null;
		foreach ( $res as $row ) {
			if ( !isset( $users[$row->user] ) ) {
				$users[$row->user] = 0;
			}
			if ( !$lastUser ) {
				$lastUser = $row->user;
			} elseif ( $lastUser !== $row->user ) {
				$lastUser = $row->user;
			}
			$users[$row->user] = 1;
		}
		return new Snapshot( $date, $this->getType(), [ 'users' => $users ] );
	}

	/**
	 * @inheritDoc
	 */
	public function aggregate(
		array $snapshots, $interval = Snapshot::INTERVAL_DAY, $date = null
	): Snapshot {
		$total = 0;
		$perUser = [];
		foreach ( $snapshots as $snapshot ) {
			$item = $snapshot->getData();
			$total += $item['total'];
			foreach ( $item['users'] as $username => $duration ) {
				if ( !isset( $perUser[$username] ) ) {
					$perUser[$username] = 0;
				}
				$perUser[$username] += $duration;
			}
		}

		return new Snapshot(
			$date ?? new SnapshotDate(), $this->getType(),
			[ 'total' => $total, 'users' => $perUser ], $interval
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getType() {
		return 'wo-logincount';
	}

	/**
	 * @inheritDoc
	 */
	public function getSecondaryData( Snapshot $snapshot ) {
		return null;
	}
}
