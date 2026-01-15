<?php

namespace MWStake\MediaWiki\Component\DataStore;

use ArrayObject;

class Schema extends ArrayObject {
	public const FILTERABLE = 'filterable';
	public const SORTABLE = 'sortable';
	public const TYPE = 'type';
	public const IS_BUCKET = 'is_bucket';

	/**
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @return array
	 */
	protected function filterFields( $key, $value ) {
		$entries = $this->filterEntries( $key, $value );
		return array_keys( $entries );
	}

	/**
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @return array
	 */
	protected function filterEntries( $key, $value ) {
		$callback = static function ( $entry ) use( $key, $value ) {
			return array_key_exists( $key, $entry )
				? $entry[$key] === $value
				: $value === false;
		};
		return array_filter( (array)$this, $callback );
	}

	/**
	 * @return string[]
	 */
	public function getUnsortableFields() {
		return $this->filterFields( self::SORTABLE, false );
	}

	/**
	 * @return string[]
	 */
	public function getUnfilterableFields() {
		return $this->filterFields( self::FILTERABLE, false );
	}

	/**
	 * @return string[]
	 */
	public function getSortableFields() {
		return $this->filterFields( self::SORTABLE, true );
	}

	/**
	 * @return string[]
	 */
	public function getFilterableFields() {
		return $this->filterFields( self::FILTERABLE, true );
	}

	/**
	 * @return string[]
	 */
	public function getBucketFields() {
		return $this->filterFields( self::IS_BUCKET, true );
	}

}
