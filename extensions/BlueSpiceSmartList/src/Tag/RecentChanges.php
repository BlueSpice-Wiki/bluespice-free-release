<?php

namespace BlueSpice\SmartList\Tag;

use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;

class RecentChanges extends SmartlistTag {

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [ 'recentchanges' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getClientTagSpecification(): ClientTagSpecification|null {
		return new ClientTagSpecification(
			'RecentChanges',
			new RawMessage( '' ),
			$this->getFormSpec(),
			Message::newFromKey( 'bs-smartlist-ve-recentchanges-title' )
		);
	}
}
