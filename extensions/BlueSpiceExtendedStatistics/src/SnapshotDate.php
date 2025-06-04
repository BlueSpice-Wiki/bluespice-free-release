<?php

namespace BlueSpice\ExtendedStatistics;

use DateTime;

class SnapshotDate extends DateTime {
	/**
	 * @param string $ts
	 * @param string $format
	 * @return static
	 * @throws \Exception
	 */
	public static function newFromFormat( $ts, $format ) {
		$date = static::createFromFormat( $format, $ts );
		return new static( $date->format( 'Y-m-d' ) );
	}

	/**
	 * @param string $ts
	 * @return static
	 * @throws \Exception
	 */
	public static function newFromMWTimestamp( $ts ) {
		$ts = str_pad( $ts, 14, '0' );
		$date = static::createFromFormat( 'YmdHis', $ts );

		return new static( $date->format( 'Y-m-d' ) );
	}

	/**
	 * @param string|null $date
	 * @throws \Exception
	 */
	public function __construct( $date = null ) {
		parent::__construct( $date ?? 'now' );
		$this->setTime( 0, 0 );
	}

	/**
	 * @return SnapshotDate
	 */
	public function getFloor(): SnapshotDate {
		return clone $this->setTime( 0, 0 );
	}

	/**
	 * @return SnapshotDate
	 */
	public function getCeiling(): SnapshotDate {
		return clone $this->setTime( 23, 59, 59 );
	}

	/**
	 * @return string
	 */
	public function mwTimestamp() {
		return $this->format( 'YmdHis' );
	}

	/**
	 * @return string
	 */
	public function forGraph() {
		return $this->format( 'Y-m-d' );
	}

	/**
	 * @return string
	 */
	public function mwDate() {
		return $this->format( 'Ymd' );
	}

	/**
	 * @param int $hour
	 * @param int $minute
	 * @param int $second
	 * @param int $microsecond
	 * @return SnapshotDate
	 */
	public function setTime( $hour, $minute, $second = 0, $microsecond = 0 ): SnapshotDate {
		return parent::setTime( $hour, $minute, $second, $microsecond );
	}
}
