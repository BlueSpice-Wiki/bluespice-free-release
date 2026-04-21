<?php

namespace MWStake\MediaWiki\Component\DataStore\Filter;

use MWStake\MediaWiki\Component\DataStore\Filter;

class Boolean extends Filter {
	public const COMPARISON_EQUALS_BOOL = '==';

	/**
	 * Performs filtering based on given filter of type bool on a dataset
	 *
	 * @param \MWStake\MediaWiki\Component\DataStore\Record $dataSet
	 * @return bool
	 */
	protected function doesMatch( $dataSet ) {
		$fieldValue = $dataSet->get( $this->getField() );
		$filterValue = $this->getValue();

		// Allow specific strings
		$fieldValue = $this->normaliseBoolean( $fieldValue );
		if ( $fieldValue === null ) {
			return false;
		}

		if ( $this->getComparison() === static::COMPARISON_EQUALS_BOOL ) {
			return $filterValue === $fieldValue;
		}

		// backwards compatibility
		switch ( $this->getComparison() ) {
			case self::COMPARISON_EQUALS:
				return $fieldValue == $filterValue;
			case self::COMPARISON_NOT_EQUALS:
				return $fieldValue != $filterValue;
		}

		return false;
	}

	/**
	 * Normalise a value to boolean
	 * Returns null if the value is invalid
	 *
	 * @param mixed $value The value to normalise
	 * @return bool|null Boolean value or null if invalid
	 */
	private function normaliseBoolean( $value ) {
		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( is_int( $value ) ) {
			if ( $value === 1 ) {
				return true;
			}
			if ( $value === 0 ) {
				return false;
			}
			return null;
		}

		if ( is_string( $value ) ) {
			$lower = strtolower( $value );
			if ( in_array( $lower, [ 'true', 'yes', '1' ] ) ) {
				return true;
			}
			if ( in_array( $lower, [ 'false', 'no', '0' ] ) ) {
				return false;
			}
		}

		return null;
	}
}
