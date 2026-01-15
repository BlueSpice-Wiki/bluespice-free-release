<?php

namespace MediaWiki\Extension\EnhancedUpload\ConfigDefinition;

use BlueSpice\ConfigDefinition\IOverwriteGlobal;
use BlueSpice\ConfigDefinition\StringSetting;

class DragDropImagesHeight extends StringSetting implements IOverwriteGlobal {
	private const EXTENSION_ENHANCED_UPLOAD = 'EnhancedUpload';

	/**
	 * @return string[]
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_EDITOR . '/' . self::EXTENSION_ENHANCED_UPLOAD,
			static::MAIN_PATH_EXTENSION . '/' . self::EXTENSION_ENHANCED_UPLOAD . '/' . static::FEATURE_EDITOR,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_FREE . '/' . self::EXTENSION_ENHANCED_UPLOAD,
		];
	}

	/**
	 * @return string
	 */
	public function getGlobalName() {
		return "wgEnhancedUploadDragDropImagesHeight";
	}

	/**
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'enhancedupload-config-height-label';
	}

	/**
	 * @return string
	 */
	public function getHelpMessageKey() {
		return 'enhancedupload-config-height-help';
	}
}
