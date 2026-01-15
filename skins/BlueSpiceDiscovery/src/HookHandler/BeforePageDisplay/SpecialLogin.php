<?php

namespace BlueSpice\Discovery\HookHandler\BeforePageDisplay;

use MediaWiki\Output\Hook\BeforePageDisplayHook;
use MediaWiki\SpecialPage\SpecialPage;

class SpecialLogin implements BeforePageDisplayHook {

	/**
	 *
	 * @inheritDoc
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		$title = $out->getTitle();
		$specialUserLogin = SpecialPage::getSafeTitleFor( 'Userlogin' );
		if ( !$specialUserLogin->equals( $title ) ) {
			return;
		}
		$out->addModuleStyles( [ 'skin.discovery.login.styles' ] );
	}
}
