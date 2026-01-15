<?php

namespace BlueSpice\Discovery\HookHandler\BeforePageDisplay;

use MediaWiki\Output\Hook\BeforePageDisplayHook;

class AddBacklink implements BeforePageDisplayHook {

	/**
	 *
	 * @inheritDoc
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		$title = $out->getTitle();
		if ( !$title->exists() && !$title->isSpecialPage() ) {
			return;
		}
		$out->addModules( [ 'skin.discovery.backlink' ] );
	}
}
