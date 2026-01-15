<?php

namespace MWStake\MediaWiki\Component\DataStore;

interface IPrimaryDataProvider {

	/**
	 *
	 * @param ReaderParams $params Having it here allows us to prefilter and
	 * tweak performance
	 * @return Record[]
	 */
	public function makeData( $params );
}
