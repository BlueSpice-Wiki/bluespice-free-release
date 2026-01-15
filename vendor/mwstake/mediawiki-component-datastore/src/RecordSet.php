<?php

namespace MWStake\MediaWiki\Component\DataStore;

class RecordSet {

	/**
	 *
	 * @var \MWStake\MediaWiki\Component\DataStore\Record[]
	 */
	protected $records = [];

	/**
	 *
	 * @param \MWStake\MediaWiki\Component\DataStore\Record[] $records
	 */
	public function __construct( $records ) {
		$this->records = $records;
	}

	/**
	 *
	 * @return \MWStake\MediaWiki\Component\DataStore\Record[]
	 */
	public function getRecords() {
		return $this->records;
	}
}
