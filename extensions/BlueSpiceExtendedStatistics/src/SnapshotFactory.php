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
			$this->bandAidData( $data );

			return new PageHitsSnapshot( $date, $data, $interval );
		}

		return new Snapshot( $date, $type, $data, $interval );
	}

	/**
	 * In case of missing props or negative hitDiff
	 * correct the data to prevent errors
	 *
	 * Previously an exception was thrown
	 *
	 * ERM36709
	 *
	 * @param array &$data
	 *
	 * @return void
	 */
	private function bandAidData( array &$data ): void {
		foreach ( $data as &$props ) {
			if ( !isset( $props[ 'hits' ] ) ) {
				$props[ 'hits' ] = 0;
			}

			if ( !isset( $props[ 'hitDiff' ] ) || $props[ 'hitDiff' ] < 0 ) {
				$props[ 'hitDiff' ] = 0;
			}
		}
	}
}
