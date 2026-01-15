<?php

namespace MWStake\MediaWiki\Component\DataStore;

class BucketedResultSet extends ResultSet {

	/**
	 *
	 * @param \MWStake\MediaWiki\Component\DataStore\Record[] $records
	 * @param int $total
	 * @param array $buckets
	 */
	public function __construct( $records, $total, array $buckets ) {
		parent::__construct( $records, $total );
		$this->buckets = $buckets;
	}

	/**
	 *
	 * @return array
	 */
	public function getBuckets() {
		return $this->buckets;
	}
}
