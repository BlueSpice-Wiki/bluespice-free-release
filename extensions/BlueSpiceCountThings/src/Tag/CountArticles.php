<?php

namespace BlueSpice\CountThings\Tag;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\GenericTag;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;

class CountArticles extends GenericTag {

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [ 'bs:countarticles', 'countarticles' ];
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
		return new CountArticlesHandler( $services->getNamespaceInfo() );
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
			'CountArticles',
			Message::newFromKey( 'bs-countthings-tag-countarticles-desc' ),
			null,
			Message::newFromKey( 'bs-countthings-ve-countarticles-title' )
		);
	}
}
