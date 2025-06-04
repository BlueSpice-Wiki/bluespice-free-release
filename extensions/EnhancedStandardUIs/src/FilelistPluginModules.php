<?php

namespace MediaWiki\Extension\EnhancedStandardUIs;

use MediaWiki\Registration\ExtensionRegistry;

class FilelistPluginModules {
	/**
	 * @return string
	 */
	public static function getModules() {
		return ExtensionRegistry::getInstance()->getAttribute(
			'EnhancedStandardUIsFilelistPluginModules'
		);
	}
}
