<?php

namespace MWStake\MediaWiki\Component\DataStore;

class ReaderParams {
	public const LIMIT_INFINITE = -1;

	public const PARAM_LIMIT = 'limit';
	public const PARAM_QUERY = 'query';
	public const PARAM_START = 'start';
	public const PARAM_SORT = 'sort';
	public const PARAM_FILTER = 'filter';
	public const PARAM_CONTINUE_FROM = 'continue';
	public const PARAM_NO_CACHE = 'no-cache';

	/**
	 * For pre filtering
	 * @var string
	 */
	protected $query = '';

	/**
	 * For paging
	 * @var int
	 */
	protected $start = 0;

	/**
	 * For paging
	 * @var int
	 */
	protected $limit = 25;

	/**
	 *
	 * @var Sort[]
	 */
	protected $sort = [];

	/**
	 *
	 * @var Filter[]
	 */
	protected $filter = [];

	/**
	 * @var array
	 */
	protected $continueFrom = [];

	/**
	 * @var bool
	 */
	protected $noCache = false;

	/**
	 *
	 * @param array $params
	 */
	public function __construct( $params = [] ) {
		$this->setIfAvailable( $this->noCache, $params, static::PARAM_NO_CACHE );
		$this->setIfAvailable( $this->query, $params, static::PARAM_QUERY );
		$this->setIfAvailable( $this->start, $params, static::PARAM_START );
		$this->setIfAvailable( $this->limit, $params, static::PARAM_LIMIT );
		$this->setIfAvailable( $this->continueFrom, $params, static::PARAM_CONTINUE_FROM );
		$this->setSort( $params );
		$this->setFilter( $params );
	}

	/**
	 *
	 * @param mixed &$property
	 * @param array $source
	 * @param string $field
	 */
	protected function setIfAvailable( &$property, $source, $field ) {
		if ( isset( $source[$field] ) ) {
			$property = $source[$field];
		}
	}

	/**
	 * Getter for "limit" param
	 * @return int The "limit" parameter
	 */
	public function getLimit() {
		return $this->limit;
	}

	/**
	 * Getter for "start" param
	 * @return int The "start" parameter
	 */
	public function getStart() {
		return $this->start;
	}

	/**
	 * Getter for "sort" param
	 * @return Sort[]
	 */
	public function getSort() {
		return $this->sort;
	}

	/**
	 * Getter for "query" param
	 * @return string The "query" parameter
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * Getter for "filter" param
	 * @return Filter[]
	 */
	public function getFilter() {
		return $this->filter;
	}

	/**
	 * @return array
	 */
	public function getContinueFrom(): array {
		return $this->continueFrom;
	}

	/**
	 * Get hash that uniquely identifies this set of parameters, for caching purposes
	 *
	 * @return string
	 */
	public function getHash(): string {
		return md5(
			json_encode( [
				'query' => $this->query,
				'sort' => $this->sort,
				'filter' => $this->filter,
			] )
		);
	}

	/**
	 * @return bool
	 */
	public function getDisableCache(): bool {
		return $this->noCache;
	}

	/**
	 *
	 * @param array $params
	 * @return void
	 */
	protected function setSort( $params ) {
		if ( !isset( $params[static::PARAM_SORT] )
			|| !is_array( $params[static::PARAM_SORT] ) ) {
			return;
		}

		$this->sort = Sort::newCollectionFromArray( $params[static::PARAM_SORT] );
	}

	/**
	 *
	 * @param array $params
	 * @return void
	 */
	protected function setFilter( $params ) {
		if ( !isset( $params[static::PARAM_FILTER] )
			|| !is_array( $params[static::PARAM_FILTER] ) ) {
			return;
		}
		$this->filter = Filter::newCollectionFromArray( $params[static::PARAM_FILTER] );
	}
}
