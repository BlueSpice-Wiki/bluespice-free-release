<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\ContentDroplets\Hook\BeforePageDisplay;

use MediaWiki\Output\Hook\BeforePageDisplayHook;
use MediaWiki\Output\OutputPage;
use Skin;

class AddModules implements BeforePageDisplayHook {

	/**
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		$out->addModules( "ext.contentdroplets.bootstrap" );
	}
}
