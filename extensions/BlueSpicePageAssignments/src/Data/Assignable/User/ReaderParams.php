<?php

namespace BlueSpice\PageAssignments\Data\Assignable\User;

use MWStake\MediaWiki\Component\DataStore\ReaderParams as Base;

class ReaderParams extends Base {

	/**
	 * @param Base $params
	 */
	public function __construct( Base $params ) {
		parent::__construct();
		$this->query = $params->getQuery();
		$this->sort = $params->getSort();
		$this->start = $params->getStart();
		$this->limit = $params->getLimit();
		$filters = $params->getFilter();
		$this->filter = [];
		foreach ( $filters as $filter ) {
			if ( $filter->getField() === 'text' ) {
				// Convert `text` filter to query
				if ( !$this->query ) {
					$this->query = $filter->getValue();
				}
			} else {
				$this->filter[] = $filter;
			}
		}
	}
}
