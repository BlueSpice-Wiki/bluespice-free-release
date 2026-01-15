<?php

namespace BlueSpice\ExtendedStatistics;

use JsonSerializable;

class Snapshot implements JsonSerializable {
	public const INTERVAL_DAY = 'day';
	public const INTERVAL_WEEK = 'week';
	public const INTERVAL_MONTH = 'month';
	public const INTERVAL_YEAR = 'year';

	/** @var SnapshotDate */
	private $date;
	/** @var string */
	private $type;
	/** @var array|null */
	protected $data;
	/** @var string */
	private $interval;

	/**
	 * @param SnapshotDate $date
	 * @param string $type
	 * @param array|null $data
	 * @param string|null $interval
	 */
	public function __construct(
		SnapshotDate $date, $type, $data = [], $interval = 'day'
	) {
		$this->date = $date;
		$this->type = $type;
		$this->data = $data;
		$this->interval = $interval;
	}

	/**
	 * @return SnapshotDate
	 */
	public function getDate(): SnapshotDate {
		return $this->date;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return array
	 */
	public function getData(): array {
		return $this->data;
	}

	/**
	 * @return string
	 */
	public function getInterval() {
		return $this->interval;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(): array {
		return [
			'date' => $this->date->mwDate(),
			'type' => $this->type,
			'data' => $this->data,
			'interval' => $this->interval
		];
	}
}
