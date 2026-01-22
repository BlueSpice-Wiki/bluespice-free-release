<?php

namespace MediaWiki\Extension\UserProfile\Hooks;

use MediaWiki\Output\Hook\BeforePageDisplayHook;

class AddModules implements BeforePageDisplayHook {

	/**
	 * @inheritDoc
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		$out->addModules( [ 'ext.userProfile.profileImage.bootstrap' ] );
		$out->addModuleStyles( [ 'ext.userProfile.styles' ] );
	}
}
