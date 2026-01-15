<?php

namespace BlueSpice\Discovery\Hook;

use BlueSpice\Discovery\EnhancedSidebar\Parser;
use MediaWiki\Storage\Hook\PageSaveCompleteHook;
use Wikimedia\ObjectCache\WANObjectCache;

class InvalidateEnhancedSidebarCache implements PageSaveCompleteHook {

	/**
	 * @param WANObjectCache $objectCache
	 */
	public function __construct( private readonly WANObjectCache $objectCache ) {
	}

	/**
	 * @inheritDoc
	 */
	public function onPageSaveComplete( $wikiPage, $user, $summary, $flags, $revisionRecord, $editResult ) {
		$title = $wikiPage->getTitle();
		if ( $title->getNamespace() !== NS_MEDIAWIKI || $title->getDBkey() !== 'Sidebar.json' ) {
			return;
		}

		$key = $this->objectCache->makeKey( Parser::CACHE_KEY, $user->getId() );
		$this->objectCache->delete( $key );
	}
}
