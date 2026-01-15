<?php

namespace BlueSpice\UserSidebar;

use MediaWiki\Config\Config;
use MediaWiki\ResourceLoader\Context as ResourceLoaderContext;

class ClientConfig {

	/**
	 * @param ResourceLoaderContext $context
	 * @param Config $config
	 * @return array
	 */
	public static function makeConfigJson(
		ResourceLoaderContext $context,
		Config $config
	) {
		$allowedUserSidebarKeywords = $config->get( 'MenuEditorUserSidebarAllowedKeywords' );
		return [
			'allowedUserSidebarKeywords' => $allowedUserSidebarKeywords
		];
	}
}
