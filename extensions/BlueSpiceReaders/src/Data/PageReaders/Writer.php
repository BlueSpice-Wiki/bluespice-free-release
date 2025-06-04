<?php

namespace BlueSpice\Readers\Data\PageReaders;

use BlueSpice\Readers\Data\Record;
use MediaWiki\Context\IContextSource;
use MWStake\MediaWiki\Component\DataStore\DatabaseWriter;
use MWStake\MediaWiki\Component\DataStore\IReader;

class Writer extends DatabaseWriter {

	/**
	 *
	 * @param IReader $reader
	 * @param \Wikimedia\Rdbms\LoadBalancer $loadBalancer
	 * @param IContextSource|null $context
	 */
	public function __construct( IReader $reader, $loadBalancer, ?IContextSource $context = null ) {
		parent::__construct( $reader, $loadBalancer, $context, $context->getConfig() );
	}

	/**
	 *
	 * @return string
	 */
	protected function getTableName() {
		return 'bs_readers';
	}

	/**
	 *
	 * @return \BlueSpice\Readers\Data\Schema
	 */
	public function getSchema() {
		return new \BlueSpice\Readers\Data\Schema();
	}

	/**
	 *
	 * @return string[]
	 */
	protected function getIdentifierFields() {
		return [ Record::REV_ID, Record::USER_ID ];
	}
}
