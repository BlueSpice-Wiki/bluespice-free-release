<?php

namespace BlueSpice\ExtendedStatistics;

interface ISnapshotStore {
	/**
	 * @param SnapshotDateRange $range
	 * @param string|null $type
	 * @param string|null $interval
	 * @return array
	 */
	public function getSnapshotForRange(
		SnapshotDateRange $range, $type = null, $interval = Snapshot::INTERVAL_DAY
	): array;

	/**
	 * @param SnapshotDate $date
	 * @param string $type
	 * @param string|null $interval
	 * @return Snapshot|null
	 */
	public function getPrevious(
		SnapshotDate $date, $type, $interval = Snapshot::INTERVAL_DAY
	);

	/**
	 * @param SnapshotDate $date
	 * @param string $type
	 * @param string $interval
	 * @return bool
	 */
	public function hasSnapshot( SnapshotDate $date, $type, $interval = Snapshot::INTERVAL_DAY );

	/**
	 * @param Snapshot $snapshot
	 * @return bool if operation was success
	 */
	public function persistSnapshot( Snapshot $snapshot );

	/**
	 * Persist any data that does not go directly to snapshot
	 * but could be used later on
	 *
	 * @param Snapshot $snapshot
	 * @param array $data
	 * @return mixed
	 */
	public function persistSecondaryData( Snapshot $snapshot, $data );
}
