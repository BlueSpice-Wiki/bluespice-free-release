<?php

namespace BlueSpice\PagesVisited\Data;

use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\DataStore\NoWriterException;

class Store extends \BlueSpice\WhoIsOnline\Data\Store {

	/**
	 *
	 * @return Reader
	 */
	public function getReader() {
		return new Reader(
			MediaWikiServices::getInstance()->getDBLoadBalancer()
		);
	}

	/**
	 *
	 * @throws NoWriterException
	 */
	public function getWriter() {
		throw new NoWriterException();
	}
}
