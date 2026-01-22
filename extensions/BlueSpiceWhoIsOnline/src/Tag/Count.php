<?php

namespace BlueSpice\WhoIsOnline\Tag;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\GenericTag;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;

class Count extends GenericTag {

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [
			'userscount',
			'bs:whoisonline:count',
			'bs:whoisonlinecount',
		];
	}

	/**
	 * @return bool
	 */
	public function hasContent(): bool {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getHandler( MediaWikiServices $services ): ITagHandler {
		return new CountHandler( $services->getService( 'BSWhoIsOnlineTracer' ) );
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
			'Whoisonlinecount',
			Message::newFromKey( 'bs-whoisonline-tag-whoisonlinecount-description' ),
			null,
			Message::newFromKey( 'bs-whoisonline-ve-whoisonlinecountinspector-title' )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getContainerElementName(): ?string {
		return 'span';
	}
}
