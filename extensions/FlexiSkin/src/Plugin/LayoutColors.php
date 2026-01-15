<?php

namespace MediaWiki\Extension\FlexiSkin\Plugin;

use MediaWiki\Extension\FlexiSkin\PluginBase;

class LayoutColors extends PluginBase {
	/**
	 * @return array with js files
	 */
	public function getJSFiles() {
		return [
			'js/ui/plugin/LayoutColors.js'
		];
	}

	/**
	 * @return array with css files
	 */
	public function getCSSFiles() {
		return [
			'all' => []
		];
	}

	/**
	 * @return string Name of the module
	 */
	public function getPluginName() {
		return 'flexiskin.ui.plugin.LayoutColors';
	}

	/**
	 * @return array valid skinnames
	 */
	public function getValidSkins() {
		return [
			'bluespicediscovery'
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getLessVarsMap(): array {
		return [
			'navbar-bg' => 'color_settings/navbar_colors/background',
			'navbar-fg' => 'color_settings/navbar_colors/foreground',
			'navbar-highlight' => 'color_settings/navbar_colors/highlight',
			'sidebar-bg' => 'color_settings/sidebar_colors/background',
			'sidebar-fg' => 'color_settings/sidebar_colors/foreground',
			'sidebar-highlight' => 'color_settings/sidebar_colors/highlight',
			'footer-bg' => 'color_settings/footer_colors/background',
			'footer-fg' => 'color_settings/footer_colors/foreground',
		];
	}
}
