<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\CategoryQueryStore;

use MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleQueryStore\Reader as TitleReader;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;

class Reader extends TitleReader {

	/**
	 * @param ReaderParams $params
	 *
	 * @return PrimaryDataProvider
	 */
	public function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider(
			$this->lb->getConnection( DB_REPLICA ), $this->getSchema(), $this->language, $this->nsInfo
		);
	}

	/**
	 * @return SecondaryDataProvider
	 */
	public function makeSecondaryDataProvider() {
		return new SecondaryDataProvider( $this->titleFactory, $this->language, $this->pageProps );
	}
}
