<?php

namespace BlueSpice\Readers\Data\PageReaders;

use BlueSpice\Readers\Data\Record;
use MediaWiki\Context\IContextSource;
use MWStake\MediaWiki\Component\DataStore\IPrimaryDataProvider;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use MWStake\MediaWiki\Component\DataStore\Record as DataStoreRecord;

class PrimaryDataProvider implements IPrimaryDataProvider {

	/**
	 *
	 * @var DataStoreRecord[]
	 */
	protected $data = [];

	/**
	 *
	 * @var \Wikimedia\Rdbms\IDatabase
	 */
	protected $db = null;

	/**
	 *
	 * @var IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @param \Wikimedia\Rdbms\IDatabase $db
	 * @param IContextSource $context
	 */
	public function __construct( $db, $context ) {
		$this->db = $db;
		$this->context = $context;
	}

	/**
	 *
	 * @param ReaderParams $params
	 * @return array
	 */
	public function makeData( $params ) {
		$this->data = [];

		$conds = [];
		$options = [
			'ORDER BY' => 'readers_ts ASC',
			'GROUP BY' => Record::USER_ID
		];

		$filters = $params->getFilter();
		foreach ( $filters as $filter ) {
			if ( $filter->getField() === Record::PAGE_ID && $filter->getComparison() === 'eq' ) {
				$pageId = $filter->getValue();
				$conds['readers_page_id'] = $pageId;
				break;
			}
		}

		$rows = $this->db->select(
			'bs_readers',
			'*',
			$conds,
			__METHOD__,
			$options
		);

		foreach ( $rows as $row ) {
			$record = new Record( $row );
			$this->appendRowToData( $record );
		}

		return $this->data;
	}

	/**
	 *
	 * @param Record $record
	 */
	protected function appendRowToData( Record $record ) {
		$this->data[] = $record;
	}
}
