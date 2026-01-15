<?php

namespace MWStake\MediaWiki\Component\DataStore;

use JsonSerializable;
use MediaWiki\Status\Status;
use stdClass;

class Record implements IRecord, JsonSerializable {

	/**
	 *
	 * @var stdClass
	 */
	protected $dataSet = null;

	/**
	 *
	 * @var Status
	 */
	protected $status = null;

	/**
	 *
	 * @param stdClass $dataSet
	 * @param Status|null $status
	 */
	public function __construct( stdClass $dataSet, ?Status $status = null ) {
		$this->dataSet = $dataSet;
		$this->status = $status;
	}

	/**
	 *
	 * @param string $fieldName
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function get( $fieldName, $default = null ) {
		if ( isset( $this->dataSet->{$fieldName} ) ) {
			return $this->dataSet->{$fieldName};
		}
		return $default;
	}

	/**
	 *
	 * @param string $fieldName
	 * @param mixed $value
	 */
	public function set( $fieldName, $value ) {
		$this->dataSet->{$fieldName} = $value;
	}

	/**
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return (array)$this->dataSet;
	}

	/**
	 *
	 * @return stdClass
	 */
	public function getData() {
		return $this->dataSet;
	}

	/**
	 *
	 * @return Status
	 */
	public function getStatus() {
		if ( $this->status ) {
			return $this->status;
		}
		$this->status = Status::newGood();
		return $this->status;
	}

}
