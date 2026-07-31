<?php

namespace BlueSpice\UserSidebar;

use MediaWiki\Extension\MenuEditor\NodeProcessor\GenericKeywordNodeProcessor;

class Extension extends \BlueSpice\Extension {

	public static function onRegistry() {
		mwsInitComponents();

		$GLOBALS['mwsgWikitextNodeProcessorRegistry'] += [
			"menu-user-sidebar-keyword" => [
				"factory" => static function () {
					return new GenericKeywordNodeProcessor(
						'menu-user-sidebar-keyword',
						[ 'YOUREDITS', 'PAGESVISITED' ]
					);
				}
			]
		];
	}
}
