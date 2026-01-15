<?php

namespace BlueSpice\PageAssignments\Data\Assignment;

use BlueSpice\PageAssignments\Data\Record;
use MediaWiki\Context\IContextSource;
use MWStake\MediaWiki\Component\DataStore\DatabaseWriter;
use MWStake\MediaWiki\Component\DataStore\IReader;
use MWStake\MediaWiki\Component\DataStore\IRecord;
use MWStake\MediaWiki\Component\DataStore\Schema;

class Writer extends DatabaseWriter {
	/**
	 *
	 * @param IReader $reader
	 * @param \Wikimedia\Rdbms\LoadBalancer $loadBalancer
	 * @param IContextSource|null $context
	 */
	public function __construct( IReader $reader, $loadBalancer,
		?IContextSource $context = null ) {
		parent::__construct( $reader, $loadBalancer, $context, $context->getConfig() );
	}

	/**
	 *
	 * @return string
	 */
	protected function getTableName() {
		return 'bs_pageassignments';
	}

	/**
	 * @return Schema Column definition compatible to
	 * https://docs.sencha.com/extjs/4.2.1/#!/api/Ext.grid.Panel-cfg-columns
	 */
	public function getSchema() {
		return new \BlueSpice\PageAssignments\Data\Schema();
	}

	/**
	 *
	 * @return array
	 */
	protected function getIdentifierFields() {
		return [ Record::PAGE_ID, Record::ASSIGNEE_KEY, Record::ASSIGNEE_TYPE ];
	}

	/**
	 *
	 * @param IRecord $record
	 * @return array
	 */
	protected function makeInsertFields( $record ) {
		return array_intersect_key(
			parent::makeInsertFields( $record ),
			array_flip( $this->getDataBaseFieldWhitelist() )
		);
	}

	/**
	 *
	 * @return array
	 */
	protected function getDataBaseFieldWhitelist() {
		return [
			Record::ASSIGNEE_KEY,
			Record::ASSIGNEE_TYPE,
			Record::PAGE_ID,
			Record::POSITION,
		];
	}

	/**
	 *
	 * @param IRecord $existingRecord
	 * @param IRecord $record
	 * @return array
	 */
	protected function makeUpdateFields( $existingRecord, $record ) {
		return array_intersect_key(
			parent::makeUpdateFields( $existingRecord, $record ),
			array_flip( $this->getDataBaseFieldWhitelist() )
		);
	}
}
