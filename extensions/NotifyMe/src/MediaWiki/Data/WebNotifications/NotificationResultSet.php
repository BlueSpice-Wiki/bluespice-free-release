<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\Data\WebNotifications;

use MWStake\MediaWiki\Component\DataStore\Record;
use MWStake\MediaWiki\Component\DataStore\ResultSet;

class NotificationResultSet extends ResultSet {
	/**
	 *
	 * @var int
	 */
	protected $processedCount = 0;

	/**
	 * @var int
	 */
	protected $itemsCount = 0;

	/**
	 *
	 * @param Record[] $records
	 * @param int $total
	 * @param int $itemsCount
	 * @param int $processedCount
	 */
	public function __construct( $records, $total, int $itemsCount, int $processedCount ) {
		parent::__construct( $records, $total );
		$this->processedCount = $processedCount;
		$this->itemsCount = $itemsCount;
	}

	/**
	 * @return int
	 */
	public function getProcessedCount(): int {
		return $this->processedCount;
	}

	/**
	 * @return int
	 */
	public function getItemsCount(): int {
		return $this->itemsCount;
	}
}
