<?php

namespace BlueSpice\ExtendedStatistics;

use InvalidArgumentException;

class PageHitsSnapshot extends Snapshot {
	public const TYPE = 'dc-pagehits';

	/**
	 * @param SnapshotDate $date
	 * @param array $data
	 * @param string $interval
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( SnapshotDate $date, array $data, string $interval ) {
		foreach ( $data as $page => $props ) {
			$data[$page] = array_merge( [
				'hits' => 0,
				'hitDiff' => 0,
				'growth' => $this->calcGrowth( $props['hits'], $props['hitDiff'] ?? 0 )
			], $props );
		}

		parent::__construct( $date, self::TYPE, $data, $interval );

		$this->validate();
	}

	/**
	 * @param int $hits
	 * @param int $hitDiff
	 *
	 * @return float
	 */
	private function calcGrowth( int $hits, int $hitDiff ): float {
		$previousHits = $hits - $hitDiff;

		if ( $previousHits === 0 ) {
			return 0;
		}

		return ( $hitDiff / $previousHits ) * 100;
	}

	/**
	 * Calc total value by hits and hitDiff
	 * over all pages
	 *
	 * @return void
	 */
	public function calcTotal(): int {
		$total = 0;

		foreach ( $this->data as $page ) {
			$total += $page['hitDiff'];
		}

		return (int)$total;
	}

	/**
	 * @return void
	 * @throws InvalidArgumentException
	 */
	private function validate(): void {
		foreach ( $this->data as $props ) {
			if (
				!isset( $props['hits'] ) || !isset( $props['hitDiff'] ) || !isset( $props['growth'] )
			) {
				throw new InvalidArgumentException( "Invalid data for PageHitsSnapshot" );
			}

			// hitDiff cant be negative
			if ( $props['hitDiff'] < 0 ) {
				$json = json_encode( $props );
				throw new InvalidArgumentException( "Invalid data for PageHitsSnapshot. hitDiff is negative: $json" );
			}
		}
	}
}
