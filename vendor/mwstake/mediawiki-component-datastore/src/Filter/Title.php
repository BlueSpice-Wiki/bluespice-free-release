<?php

namespace MWStake\MediaWiki\Component\DataStore\Filter;

use MediaWiki\Title\Title as MediaWikiTitle;

class Title extends Range {

	/**
	 * Performs string filtering based on given filter of type Title on a
	 * dataset
	 *
	 * @param \MWStake\MediaWiki\Component\DataStore\Record $dataSet
	 * @return bool
	 */
	protected function doesMatch( $dataSet ) {
		if ( !is_string( $this->getValue() ) ) {
			// TODO: Warning
			return true;
		}
		$fieldValue = MediaWikiTitle::newFromText(
			$dataSet->get( $this->getField() ),
			$this->getDefaultTitleNamespace()
		);
		$filterValue = MediaWikiTitle::newFromText(
			$this->getValue(),
			$this->getDefaultTitleNamespace()
		);

		switch ( $this->getComparison() ) {
			case self::COMPARISON_GREATER_THAN:
				return MediaWikiTitle::compare( $fieldValue, $filterValue ) > 0;
			case self::COMPARISON_LOWER_THAN:
				return MediaWikiTitle::compare( $fieldValue, $filterValue ) < 0;
			case self::COMPARISON_EQUALS:
				return MediaWikiTitle::compare( $fieldValue, $filterValue ) == 0;
			case self::COMPARISON_NOT_EQUALS:
				return MediaWikiTitle::compare( $fieldValue, $filterValue ) != 0;
		}
		return true;
	}

	/**
	 *
	 * @return int
	 */
	protected function getDefaultTitleNamespace() {
		return NS_MAIN;
	}

}
