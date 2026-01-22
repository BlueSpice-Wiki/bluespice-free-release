<?php

namespace BlueSpice\ArticleInfo\Hook;

use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Storage\Hook\PageSaveCompleteHook;
use MWStake\MediaWiki\Component\Wire\WireChannelFactory;
use MWStake\MediaWiki\Component\Wire\WireMessage;
use MWStake\MediaWiki\Component\Wire\WireMessenger;

class RevisionSaved implements PageSaveCompleteHook {

	/**
	 * @param WireMessenger $wireMessenger
	 * @param HookContainer $hookContainer
	 */
	public function __construct(
		private readonly WireMessenger $wireMessenger,
		private readonly HookContainer $hookContainer
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function onPageSaveComplete( $wikiPage, $user, $summary, $flags, $revisionRecord, $editResult ) {
		if ( $editResult->isNullEdit() || $editResult->isNew() ) {
			return;
		}
		$skip = false;
		$this->hookContainer->run( 'BSArticleInfoSkip', [ $wikiPage->getTitle(), &$skip ] );
		if ( $skip ) {
			return;
		}

		$wireMessage = new WireMessage(
			( new WireChannelFactory() )->getChannelForPage( $wikiPage->getTitle() ),
			[
				'action' => 'revisionSaved',
				'message' => 'bs-articleinfo-newrevision-info-text-reload',
				'page' => $wikiPage->getTitle()->getPrefixedDBkey(),
				'editor' => $user->getName(),
			]
		);
		$this->wireMessenger->send( $wireMessage );
	}
}
