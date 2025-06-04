<?php

namespace BlueSpice\WhoIsOnline\Data;

use MWStake\MediaWiki\Component\DataStore\DatabaseWriter;

class Writer extends DatabaseWriter {

	/**
	 *
	 * @return string[]
	 */
	protected function getIdentifierFields() {
		return [ Record::ID ];
	}

	/**
	 *
	 * @return string
	 */
	protected function getTableName() {
		return Schema::TABLE_NAME;
	}

	/**
	 *
	 * @return Schema
	 */
	public function getSchema() {
		return new Schema;
	}

}
