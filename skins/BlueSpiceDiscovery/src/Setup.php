<?php

namespace BlueSpice\Discovery;

use MediaWiki\Registration\ExtensionRegistry;

class Setup {

	/**
	 *
	 */
	public static function onCallback() {
		self::addBootstrapAssetsToSpecialVersion();

		\mwsInitComponents();

		$GLOBALS['wgUseMediaWikiUIEverywhere'] = true;
		$GLOBALS['wgVisualEditorSupportedSkins'][] = 'bluespicediscovery';
		$GLOBALS['wgVisualEditorSkinToolbarScrollOffset']['bluespicediscovery'] = 100;

		global $wgScriptPath;
		$GLOBALS['wgFavicon'] = "$wgScriptPath/skins/BlueSpiceDiscovery/resources/images/favicon.ico";

		$GLOBALS['wgServiceWiringFiles'][] = dirname( __DIR__ ) . '/includes/ServiceWiring.php';
		$skinSlots = new SkinSlots();
		$skinSlots->init();

		$GLOBALS['mwsgWikitextNodeProcessorRegistry'] += ExtensionRegistry::getInstance()->getAttribute(
			"BlueSpiceDiscoveryEnhancedSidebarNodeProcessors"
		);

		// Allow language selector to know the correct language based on subpages ERM36861
		$GLOBALS['wgPageLanguageUseDB'] = true;
	}

	/**
	 * @return void
	 */
	protected static function addBootstrapAssetsToSpecialVersion(): void {
		$path = $GLOBALS['IP'];
		$path .= '/skins/BlueSpiceDiscovery/resources/bootstrap/icons/LICENSE';
		$GLOBALS['wgExtensionCredits']['bluespice-assets'][] = [
			'path' => $path,
			'name' => 'Twitter Bootstrap Icons',
			'license-name' => 'MIT',
			'author' => 'Twitter Bootstrap',
			'url' => 'https://icons.getbootstrap.com',
			'descriptionmsg' => 'bs-discovery-ext-credentials-desc-bootstrap-icons',
			'version' => 'v1.10.3',
		];

		$path = $GLOBALS['IP'];
		$path .= '/skins/BlueSpiceDiscovery/resources/bootstrap/dist/LICENCE';
		$GLOBALS['wgExtensionCredits']['bluespice-assets'][] = [
			'path' => $path,
			'name' => 'Twitter Bootstrap',
			'license-name' => 'MIT',
			'author' => 'Twitter Bootstrap',
			'url' => 'https://getbootstrap.com',
			'descriptionmsg' => 'bs-discovery-ext-credentials-desc-bootstrap-dist',
			'version' => 'v5.3.3',
		];

		// License for "Hyperlegible" font
		$path = $GLOBALS['IP'];
		$path .= '/skins/BlueSpiceDiscovery/resources/fonts/Hyperlegible/LICENSE';
		$GLOBALS['wgExtensionCredits']['bluespice-assets'][] = [
			'path' => $path,
			'name' => 'The Atkinson Hyperlegible Next Font',
			'license-name' => 'SIL Open Font License',
			'author' => 'Braille Institute of America, Inc.',
			'url' => 'https://www.brailleinstitute.org/',
			'descriptionmsg' => 'bs-discovery-ext-credentials-desc-hyperlegible-font',
			'version' => '',
		];
	}
}
