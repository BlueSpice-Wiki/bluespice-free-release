<?php

namespace MediaWiki\Extension\EnhancedStandardUIs;

use MediaWiki\MediaWikiServices;

class AddAllPagesPaginatorOffset {
	/**
	 * @return array
	 */
	public static function makeConfigJson() {
		$mainConfig = MediaWikiServices::getInstance()->getMainConfig();
		$offset = $mainConfig->get( 'EnhancedUIsAllPagesPaginatorOffset' );

		return [
			'offsetHeight' => $offset
		];
	}
}
