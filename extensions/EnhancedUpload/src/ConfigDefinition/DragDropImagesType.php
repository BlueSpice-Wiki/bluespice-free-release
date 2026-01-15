<?php

namespace MediaWiki\Extension\EnhancedUpload\ConfigDefinition;

use BlueSpice\ConfigDefinition\ArraySetting;
use BlueSpice\ConfigDefinition\IOverwriteGlobal;
use MediaWiki\HTMLForm\Field\HTMLSelectField;

class DragDropImagesType extends ArraySetting implements IOverwriteGlobal {
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
	 * @return HTMLSelectField
	 */
	public function getHtmlFormField() {
		return new HTMLSelectField( $this->makeFormFieldParams() );
	}

	/**
	 * @return string[]
	 */
	public function getOptions() {
		return [
			'thumb' => 'thumb',
			'frameless' => 'frameless',
			'frame' => 'frame',
			'basic' => 'none',
		];
	}

	/**
	 * @return string
	 */
	public function getGlobalName() {
		return "wgEnhancedUploadDragDropImagesType";
	}

	/**
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'enhancedupload-config-type-label';
	}

	/**
	 * @return string
	 */
	public function getHelpMessageKey() {
		return 'enhancedupload-config-type-help';
	}
}
