<?php

namespace BlueSpice\Readers\Data\RevisionReaders;

use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\DataStore\DatabaseReader;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;

class Reader extends DatabaseReader {

	/**
	 *
	 * @param ReaderParams $params
	 * @return PrimaryDataProvider
	 */
	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->db, $this->context );
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
	 * @return SecondaryDataProvider
	 */
	public function makeSecondaryDataProvider() {
		if ( $this->context instanceof IContextSource ) {
			$language = $this->context->getLanguage();
		} else {
			$language = RequestContext::getMain()->getLanguage();
		}
		return new SecondaryDataProvider(
			$language,
			MediaWikiServices::getInstance()->getRevisionLookup()
		);
	}

}
