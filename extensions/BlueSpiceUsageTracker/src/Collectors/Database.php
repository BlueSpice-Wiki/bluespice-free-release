<?php
namespace BS\UsageTracker\Collectors;

use BS\UsageTracker\CollectorResult;

class Database extends Base {

	protected $table;
	protected $uniqueColumns;
	protected $condition;
	protected $data;
	protected $multipledata;
	protected $column;
	protected $description = 'bs-usagetracker-db-collector-desc';

	/**
	 *
	 * @param array $config
	 */
	public function __construct( $config = [] ) {
		parent::__construct( $config );
		if ( isset( $config['config'] ) && is_array( $config['config'] ) ) {
			if ( isset( $config['config']['table'] ) ) {
				$this->table = $config['config']['table'];
			}
			if ( isset( $config['config']['uniqueColumns'] ) ) {
				$this->uniqueColumns =
					is_array( $config['config']['uniqueColumns'] )
					? $config['config']['uniqueColumns']
					: [ $config['config']['uniqueColumns'] ];
			}
			if ( isset( $config['config']['condition'] ) ) {
				$this->condition =
					is_array( $config['config']['condition'] )
					? $config['config']['condition']
					: [ $config['config']['condition'] ];
			}
			if ( isset( $config['config']['multipledata'] ) ) {
				$this->multipledata =
					is_array( $config['config']['multipledata'] )
					? $config['config']['multipledata']
					: [ $config['config']['multipledata'] ];
			}
			if ( isset( $config['config']['column'] ) ) {
				$this->column =
					is_array( $config['config']['column'] )
					? $config['config']['column']
					: [ $config['config']['column'] ];
			}
		}
	}

	/**
	 *
	 * @return \BS\UsageTracker\CollectorResult
	 * @throws \MWException
	 */
	public function getUsageData() {
		$oRes = new \BS\UsageTracker\CollectorResult( $this );
		if ( !$this->table || !$this->uniqueColumns ) {
			throw new \MWException( "UsageTracker::DatabaseCollector: table or columns are not set." );
		}

		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		$res = $dbr->select(
			[ $this->table ],
			$this->uniqueColumns,
			$this->condition ?? [],
			__METHOD__,
			$this->uniqueColumns[0] != '*' ? [ "GROUP BY" => $this->uniqueColumns ] : []
		);
		if ( $this->multipledata ) {
			$objkey = $this->column[0];
			foreach ( $res as $row ) {
				$this->data[$row->$objkey][] = $row;
			}
			$prefix = $oRes->identifier;
			$contentarray = [];
			foreach ( $this->data as $key => $val ) {
				array_push( $contentarray, $this->getCollectorData( $key, $val, $oRes, $prefix ) );

			}
			return $contentarray;
		} else {
			$oRes->count = $res->numRows();
			return $oRes;
		}
	}

	/**
	 * @param string $key
	 * @param array $val
	 * @param CollectorResult $res
	 * @param string $prefix
	 * @return array
	 */
	protected function getCollectorData( $key, $val, $res, $prefix ) {
		$contentarray = [];
		$res->count = count( $val );
		$res->identifier = $prefix . "." . ( $key );
		return array_merge( $contentarray, (array)$res );
	}
}
