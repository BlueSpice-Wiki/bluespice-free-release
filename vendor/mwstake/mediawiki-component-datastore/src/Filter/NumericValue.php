<?php

namespace MWStake\MediaWiki\Component\DataStore\Filter;

class NumericValue extends Range {
	/**
	 * Performs numeric filtering based on given filter of type integer on a
	 * dataset
	 *
	 * @param \MWStake\MediaWiki\Component\DataStore\Record $dataSet
	 * @return bool
	 */
	protected function doesMatch( $dataSet ) {
		if ( !is_numeric( $this->getValue() ) ) {
			// TODO: Warning
			return true;
		}
		$fieldValue = (int)$dataSet->get( $this->getField() );
		$filterValue = (int)$this->getValue();

		switch ( $this->getComparison() ) {
			case self::COMPARISON_GREATER_THAN:
				return $fieldValue > $filterValue;
			case self::COMPARISON_LOWER_THAN:
				return $fieldValue < $filterValue;
			case self::COMPARISON_EQUALS:
				return $fieldValue === $filterValue;
			case self::COMPARISON_NOT_EQUALS:
				return $fieldValue !== $filterValue;
		}
		return true;
	}
}
