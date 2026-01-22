<?php

namespace BlueSpice\CountThings\Tag;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\GenericTag;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;

class CountUsers extends GenericTag {

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [ 'bs:countusers', 'countusers' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getContainerElementName(): ?string {
		return 'span';
	}

	/**
	 * @inheritDoc
	 */
	public function hasContent(): bool {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getHandler( MediaWikiServices $services ): ITagHandler {
		return new CountUsersHandler();
	}

	/**
	 * @inheritDoc
	 */
	public function getParamDefinition(): ?array {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function getClientTagSpecification(): ClientTagSpecification|null {
		return new ClientTagSpecification(
			'CountUsers',
			Message::newFromKey( 'bs-countthings-tag-countusers-desc' ),
			null,
			Message::newFromKey( 'bs-countthings-ve-countusers-title' )
		);
	}
}
