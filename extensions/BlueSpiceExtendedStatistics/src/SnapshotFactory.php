<?php

namespace BlueSpice\ExtendedStatistics;

class SnapshotFactory {

	/**
	 * @param SnapshotDate $date
	 * @param string $type
	 * @param array $data
	 * @param string $interval
	 *
	 * @return Snapshot
	 */
	public function createSnapshot(
		SnapshotDate $date,
		string $type,
		array $data,
		string $interval = Snapshot::INTERVAL_DAY
	): Snapshot {
		if ( $type === PageHitsSnapshot::TYPE ) {
			return new PageHitsSnapshot( $date, $data, $interval );
		}

		return new Snapshot( $date, $type, $data, $interval );
	}
}
