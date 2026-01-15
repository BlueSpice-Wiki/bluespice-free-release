<?php

namespace MWStake\MediaWiki\Component\DataStore\Filter;

use MWStake\MediaWiki\Component\DataStore\Filter;

/**
 * Class name "String" is reserved
 */
class StringValue extends Filter {
	public const COMPARISON_STARTS_WITH = 'sw';
	public const COMPARISON_ENDS_WITH = 'ew';
	public const COMPARISON_NOT_CONTAINS = 'nct';

	/**
	 * Performs string filtering based on given filter of type string on a
	 * dataset
	 * @param \MWStake\MediaWiki\Component\DataStore\Record $dataSet
	 * @return bool
	 */
	protected function doesMatch( $dataSet ) {
		$fieldValues = $dataSet->get( $this->getField() );
		if ( !is_array( $fieldValues ) ) {
			$fieldValues = [ $fieldValues ];
		}
		foreach ( $fieldValues as $fieldValue ) {
			if ( !is_scalar( $fieldValue ) ) {
				continue;
			}
			$res = $this->compareStrings(
				$this->getComparison(),
				(string)$fieldValue,
				$this->getValue()
			);
			if ( $res ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Case-insensitive optional string comparison function
	 *
	 * @param string $operation - comparison option
	 * @param string $haystack
	 * @param string $needle
	 * @return bool
	 */
	private function compareStrings( string $operation, string $haystack, string $needle ): bool {
		$haystack = mb_strtolower( $haystack );
		$needle = mb_strtolower( $needle );

		switch ( $operation ) {
			case self::COMPARISON_STARTS_WITH:
				return $needle === '' ||
					strrpos( $haystack, $needle, -strlen( $haystack ) ) !== false;
			case self::COMPARISON_ENDS_WITH:
				$needleLen = strlen( $needle );
				if ( $needleLen < 1 ) {
					return true;
				}
				return substr( $haystack, -$needleLen ) === $needle;
			case self::COMPARISON_CONTAINS:
			case self::COMPARISON_LIKE:
				return strpos( $haystack, $needle ) !== false;
			case self::COMPARISON_NOT_CONTAINS:
				return strpos( $haystack, $needle ) === false;
			case self::COMPARISON_EQUALS:
				return $haystack === $needle;
			case self::COMPARISON_NOT_EQUALS:
				return $haystack !== $needle;
			default:
				return false;
		}
	}
}
