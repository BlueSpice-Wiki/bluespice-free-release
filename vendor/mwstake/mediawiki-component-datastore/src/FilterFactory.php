<?php

namespace MWStake\MediaWiki\Component\DataStore;

use UnexpectedValueException;

class FilterFactory {
	/**
	 *
	 * @return array
	 */
	public static function getTypeMap() {
		return [
			'string' => 'MWStake\MediaWiki\Component\DataStore\Filter\StringValue',
			'date' => 'MWStake\MediaWiki\Component\DataStore\Filter\Date',
			# 'datetime'=> 'MWStake\MediaWiki\Component\DataStore\Filter\DateTime',
			'boolean' => 'MWStake\MediaWiki\Component\DataStore\Filter\Boolean',
			'numeric' => 'MWStake\MediaWiki\Component\DataStore\Filter\NumericValue',
			'title' => 'MWStake\MediaWiki\Component\DataStore\Filter\Title',
			'templatetitle' => 'MWStake\MediaWiki\Component\DataStore\Filter\TemplateTitle',
			'list' => 'MWStake\MediaWiki\Component\DataStore\Filter\ListValue'
		];
	}

	/**
	 *
	 * @param array $filter
	 * @return \MWStake\MediaWiki\Component\DataStore\Filter
	 * @throws UnexpectedValueException
	 */
	public static function newFromArray( $filter ) {
		$typeMap = static::getTypeMap();
		if ( isset( $typeMap[$filter[Filter::KEY_TYPE]] ) ) {
			return new $typeMap[$filter[Filter::KEY_TYPE]]( $filter );
		} else {
			throw new UnexpectedValueException(
				"No filter class for '{$filter[Filter::KEY_TYPE]}' available!"
			);
		}
	}
}
