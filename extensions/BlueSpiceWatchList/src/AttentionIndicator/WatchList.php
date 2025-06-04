<?php

namespace BlueSpice\WatchList\AttentionIndicator;

use BlueSpice\Data\Watchlist\Record;
use BlueSpice\Data\Watchlist\Store;
use BlueSpice\Discovery\AttentionIndicator;
use MediaWiki\Context\RequestContext;
use MWStake\MediaWiki\Component\DataStore\FieldType;
use MWStake\MediaWiki\Component\DataStore\Filter\Boolean;
use MWStake\MediaWiki\Component\DataStore\Filter\Numeric;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;

class WatchList extends AttentionIndicator {

	/**
	 * @return array
	 */
	protected function makeReaderParams(): array {
		return [
			ReaderParams::PARAM_FILTER => [ [
					Boolean::KEY_COMPARISON => Boolean::COMPARISON_EQUALS,
					Boolean::KEY_PROPERTY => Record::HAS_UNREAD_CHANGES,
					Boolean::KEY_TYPE => FieldType::BOOLEAN,
					Boolean::KEY_VALUE => true,
				], [
					Numeric::KEY_COMPARISON => Numeric::COMPARISON_EQUALS,
					Numeric::KEY_PROPERTY => Record::USER_ID,
					Numeric::KEY_TYPE => 'numeric',
					Numeric::KEY_VALUE => $this->user->getId(),
				],
			],
			ReaderParams::PARAM_LIMIT => ReaderParams::LIMIT_INFINITE,
		];
	}

	/**
	 * @return int
	 */
	protected function doIndicationCount(): int {
		$res = ( new Store( RequestContext::getMain(), false ) )->getReader()->read(
			new ReaderParams( $this->makeReaderParams() )
		);
		return $res->getTotal();
	}

}
