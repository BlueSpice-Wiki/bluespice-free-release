<?php

namespace MediaWiki\Extension\EnhancedStandardUIs;

use MediaWiki\MediaWikiServices;

class AddVersionHistoryToolbarOffset {
	/**
	 * @return array
	 */
	public static function makeConfigJson() {
		$mainConfig = MediaWikiServices::getInstance()->getMainConfig();
		$offset = $mainConfig->get( 'EnhancedUIsVersionHistoryToolbarOffset' );

		return [
			'offsetHeight' => $offset
		];
	}
}
