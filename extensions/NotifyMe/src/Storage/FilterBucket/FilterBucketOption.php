<?php

namespace MediaWiki\Extension\NotifyMe\Storage\FilterBucket;

use MediaWiki\Message\Message;

class FilterBucketOption {

	/** @var Message */
	private $label;

	/** @var string */
	private $dataKey;

	/** @var int */
	private $count;

	/**
	 * @param Message $label
	 * @param string $key
	 * @param int $count
	 */
	public function __construct( Message $label, string $key, int $count ) {
		$this->label = $label;
		$this->dataKey = $key;
		$this->count = $count;
	}

	/**
	 * @return int
	 */
	public function getCount(): int {
		return $this->count;
	}

	/**
	 * @return string
	 */
	public function getDataKey(): string {
		return $this->dataKey;
	}

	/**
	 * @return Message
	 */
	public function getLabel(): Message {
		return $this->label;
	}
}
