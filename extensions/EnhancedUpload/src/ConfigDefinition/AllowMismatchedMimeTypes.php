<?php

namespace MediaWiki\Extension\EnhancedUpload\ConfigDefinition;

use BlueSpice\ConfigDefinition\BooleanSetting;
use BlueSpice\ConfigDefinition\IOverwriteGlobal;

class AllowMismatchedMimeTypes extends BooleanSetting implements IOverwriteGlobal {

	private const EXTENSION_ENHANCED_UPLOAD = 'EnhancedUpload';

	/**
	 * @return string[]
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_SYSTEM . '/' . self::EXTENSION_ENHANCED_UPLOAD,
			static::MAIN_PATH_EXTENSION . '/' . self::EXTENSION_ENHANCED_UPLOAD . '/' . static::FEATURE_SYSTEM,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/' . self::EXTENSION_ENHANCED_UPLOAD,
		];
	}

	/**
	 * @return string
	 */
	public function getGlobalName() {
		return "wgEnhancedUploadAllowMismatchedMimeTypes";
	}

	/**
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'enhancedupload-config-extensions-label';
	}

	/**
	 * @return string
	 */
	public function getHelpMessageKey() {
		return 'enhancedupload-config-extensions-help';
	}
}
