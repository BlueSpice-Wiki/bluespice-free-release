<?php

namespace MediaWiki\Extension\EnhancedUpload\ConfigDefinition;

use BlueSpice\ConfigDefinition\ArraySetting;
use BlueSpice\ConfigDefinition\IOverwriteGlobal;
use MediaWiki\HTMLForm\Field\HTMLSelectField;

class DragDropImagesAlignment extends ArraySetting implements IOverwriteGlobal {
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
			'left' => 'left',
			'right' => 'right',
			'center' => 'center',
			'unwrap text' => 'none'
		];
	}

	/**
	 * @return string
	 */
	public function getGlobalName() {
		return "wgEnhancedUploadDragDropImagesAlignment";
	}

	/**
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'enhancedupload-config-alignment-label';
	}

	/**
	 * @return string
	 */
	public function getHelpMessageKey() {
		return 'enhancedupload-config-alignment-help';
	}
}
