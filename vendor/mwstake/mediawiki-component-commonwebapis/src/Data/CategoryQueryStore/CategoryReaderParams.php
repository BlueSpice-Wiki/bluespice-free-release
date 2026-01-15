<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\CategoryQueryStore;

use MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleQueryStore\TitleRecord;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;

class CategoryReaderParams extends ReaderParams {

	/**
	 * @param ReaderParams $params
	 * @return static
	 */
	public static function newFromOtherReaderParams( ReaderParams $params ) {
		return new static( [
			ReaderParams::PARAM_FILTER => static::setCategoryFilter( $params ),
			ReaderParams::PARAM_SORT => $params->getSort(),
			ReaderParams::PARAM_START => $params->getStart(),
			ReaderParams::PARAM_LIMIT => $params->getLimit(),
			ReaderParams::PARAM_QUERY => $params->getQuery()
		] );
	}

	/**
	 *
	 * @param array $params
	 */
	public function __construct( $params = [] ) {
		parent::__construct();
		$this->setIfAvailable( $this->query, $params, static::PARAM_QUERY );
		$this->setIfAvailable( $this->start, $params, static::PARAM_START );
		$this->setIfAvailable( $this->limit, $params, static::PARAM_LIMIT );
		$this->setIfAvailable( $this->sort, $params, static::PARAM_SORT );
	}

	/**
	 *
	 * @param ReaderParams $params
	 * @return array
	 */
	protected static function setCategoryFilter( ReaderParams $params ): array {
		$filters = $params->getFilter();
		$newFilters = [];
		foreach ( $filters as $filter ) {
			if ( $filter->getField() !== TitleRecord::PAGE_NAMESPACE ) {
				$newFilters[] = $filter;
			}
		}
		return $newFilters;
	}

}
