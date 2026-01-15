<?php

namespace MWStake\MediaWiki\Component\DataStore;

class LimitOffsetTrimmer implements ITrimmer {

	/**
	 *
	 * @var int
	 */
	protected $limit = 25;

	/**
	 *
	 * @var int
	 */
	protected $offset = 0;

	/**
	 *
	 * @param int $limit
	 * @param int $offset
	 */
	public function __construct( $limit = 25, $offset = 0 ) {
		$this->limit = $limit;
		$this->offset = $offset;
	}

	/**
	 *
	 * @param \MWStake\MediaWiki\Component\DataStore\Record[] $dataSets
	 * @return \MWStake\MediaWiki\Component\DataStore\Record[]
	 */
	public function trim( $dataSets ) {
		$total = count( $dataSets );
		$end = $total;
		if ( $this->limit !== ReaderParams::LIMIT_INFINITE ) {
			$end = $this->limit + $this->offset;
		}

		if ( $end > $total || $end === 0 ) {
			$end = $total;
		}

		$trimmedData = [];
		for ( $i = $this->offset; $i < $end; $i++ ) {
			$trimmedData[] = $dataSets[$i];
		}

		return array_values( $trimmedData );
	}
}
