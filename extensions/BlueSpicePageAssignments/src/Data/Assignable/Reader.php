<?php

namespace BlueSpice\PageAssignments\Data\Assignable;

use BlueSpice\PageAssignments\Data\Schema;
use MWStake\MediaWiki\Component\DataStore\DatabaseReader;
use MWStake\MediaWiki\Component\DataStore\Tests\ReaderParamsTest;

class Reader extends DatabaseReader {

	/**
	 *
	 * @param ReaderParamsTest $params
	 * @return PrimaryDataProvider
	 */
	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->db, $this->context );
	}

	/**
	 *
	 * @return Schema
	 */
	public function getSchema() {
		return new Schema();
	}

	/**
	 *
	 * @return null
	 */
	public function makeSecondaryDataProvider() {
		return null;
	}

}
