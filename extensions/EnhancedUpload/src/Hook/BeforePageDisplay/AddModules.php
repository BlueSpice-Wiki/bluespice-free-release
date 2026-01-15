<?php

namespace MediaWiki\Extension\EnhancedUpload\Hook\BeforePageDisplay;

use MediaWiki\Output\Hook\BeforePageDisplayHook;
use MediaWiki\Output\OutputPage;

class AddModules implements BeforePageDisplayHook {

	/**
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return void
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		$out->addModules( 'ext.enhancedUpload.uploadDialog.bootstrap' );
	}
}
