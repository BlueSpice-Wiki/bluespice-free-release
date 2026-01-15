<?php

namespace MediaWiki\Extension\PDFCreator\ConfigDefinition;

use BlueSpice\ConfigDefinition\BooleanSetting;
use BlueSpice\ConfigDefinition\IOverwriteGlobal;

class SuppressNamespace extends BooleanSetting implements IOverwriteGlobal {

	/**
	 *
	 * @return array
	 */
	public function getPaths() {
		$feature = static::FEATURE_EXPORT;
		$ext = 'PDFCreator';
		$package = static::PACKAGE_PRO;
		return [
			static::MAIN_PATH_FEATURE . "/$feature/$ext",
			static::MAIN_PATH_EXTENSION . "/$ext/$feature",
			static::MAIN_PATH_PACKAGE . "/$package/$ext",
		];
	}

	/**
	 * @return string
	 */
	public function getGlobalName() {
		return "wgPDFCreatorSuppressNamespace";
	}

	/**
	 *
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'pdfcreator-config-suppress-namespace';
	}

	/**
	 *
	 * @return string
	 */
	public function getHelpMessageKey() {
		return 'pdfcreator-config-suppress-namespace-help';
	}

}
