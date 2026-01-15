<?php

namespace BlueSpice\WhoIsOnline\Data\Tracer;

use MWStake\MediaWiki\Component\DataStore\ReaderParams;

class Reader extends \BlueSpice\WhoIsOnline\Data\Reader {

	/**
	 *
	 * @param ReaderParams $params
	 * @return PrimaryDataProvider
	 */
	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->db, $this->getSchema() );
	}

}
