<?php

namespace MWStake\MediaWiki\Component\DataStore\Filter;

use MWStake\MediaWiki\Component\DataStore\Filter;

class ListValue extends Filter {
	public const COMPARISON_IN = 'in';
	public const COMPARISON_CONTAINS = 'ct';
	public const COMPARISON_NOT_CONTAINS = 'nct';

	/**
	 *
	 * @param array $params
	 */
	public function __construct( $params ) {
		if ( !isset( $params[self::KEY_COMPARISON] ) ) {
			$params[self::KEY_COMPARISON] = static::COMPARISON_IN;
		}
		parent::__construct( $params );
	}

	/**
	 * Performs list filtering based on given filter of type array on a dataset
	 * @param \MWStake\MediaWiki\Component\DataStore\Record $dataSet
	 * @return bool
	 */
	protected function doesMatch( $dataSet ) {
		if ( !is_array( $this->getValue() ) ) {
			// TODO: Warning
			return true;
		}
		$fieldValues = $dataSet->get( $this->getField() );
		// empty( false ) = true
		if ( is_bool( $fieldValues ) ) {
			$fieldValues = [ $fieldValues ? 'true' : 'false' ];
		}
		if ( empty( $fieldValues ) ) {
			return false;
		}
		if ( is_string( $fieldValues ) ) {
			$fieldValues = [ $fieldValues ];
		}

		$intersection = array_intersect( $fieldValues, $this->getValue() );

		if ( $this->getComparison() === static::COMPARISON_CONTAINS
			|| $this->getComparison() === static::COMPARISON_IN ) {
			if ( empty( $intersection ) ) {
				return false;
			}
		}
		if ( $this->getComparison() === static::COMPARISON_NOT_CONTAINS && !empty( $intersection ) ) {
			return false;
		}
		return true;
	}
}
