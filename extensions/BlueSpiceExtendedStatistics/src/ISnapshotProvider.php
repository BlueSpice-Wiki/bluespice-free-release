<?php

namespace BlueSpice\ExtendedStatistics;

interface ISnapshotProvider {
	/**
	 * @param SnapshotDate $date
	 * @return Snapshot
	 */
	public function generateSnapshot( SnapshotDate $date ): Snapshot;

	/**
	 * Aggregate multiple snapshots into one
	 *
	 * @param Snapshot[] $snapshots
	 * @param string|null $interval
	 * @param SnapshotDate|null $date Date for aggregation, null if on-the-fly aggregation
	 * @return Snapshot
	 */
	public function aggregate(
		array $snapshots, $interval = Snapshot::INTERVAL_DAY, $date = null
	): Snapshot;

	/**
	 * Data to be recorded, but not official part of the snapshot
	 * @param Snapshot $snapshot
	 * @return array|null
	 */
	public function getSecondaryData( Snapshot $snapshot );

	/**
	 * Return type key for the provider
	 *
	 * @return string
	 */
	public function getType();
}
