<?php

namespace BlueSpice\WhoIsOnline\Data;

use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\DataStore\IStore;

class Store implements IStore {

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
	 * @return Writer
	 */
	public function getWriter() {
		return new Writer(
			$this->getReader(),
			MediaWikiServices::getInstance()->getDBLoadBalancer()
		);
	}
}
