<?php

namespace BlueSpice\PagesVisited\Hook;

use BlueSpice\PagesVisited\Tag\PagesVisited;
use MediaWiki\Language\Language;
use MediaWiki\Title\NamespaceInfo;
use MWStake\MediaWiki\Component\GenericTagHandler\Hook\MWStakeGenericTagHandlerInitTagsHook;

class RegisterTags implements MWStakeGenericTagHandlerInitTagsHook {

	/**
	 * @param NamespaceInfo $namespaceInfo
	 * @param Language $language
	 */
	public function __construct(
		private readonly NamespaceInfo $namespaceInfo,
		private readonly Language $language
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function onMWStakeGenericTagHandlerInitTags( array &$tags ) {
		$tags[] = new PagesVisited( $this->namespaceInfo, $this->language );
	}
}
