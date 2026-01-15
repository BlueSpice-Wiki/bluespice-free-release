<?php

use BlueSpice\Discovery\AttentionIndicatorFactory;
use BlueSpice\Discovery\BackLinkProviderFactory;
use BlueSpice\Discovery\BreadcrumbDataProviderFactory;
use BlueSpice\Discovery\CookieHandler;
use BlueSpice\Discovery\LangLinksProviderFactory;
use BlueSpice\Discovery\MenuManager;
use BlueSpice\Discovery\MenuProviderFactory;
use BlueSpice\Discovery\MetaItemsManager;
use BlueSpice\Discovery\MetaItemsProviderFactory;
use BlueSpice\Discovery\Renderer\ComponentRenderer;
use BlueSpice\Discovery\Renderer\SkinSlotRenderer;
use BlueSpice\Discovery\TemplateDataProviderFactory;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Request\WebRequest;

return [
	'BlueSpiceDiscoveryTemplateDataProvider' => static function ( MediaWikiServices $services ) {
		$factory = $services->getService( 'BlueSpiceDiscoveryTemplateDataProviderFactory' );
		$config = $services->getConfigFactory()->makeConfig( 'bsg' );
		$providerFromConfig = $config->get( 'DiscoveryTemplateDataProvider' );
		$panelManager = $factory->getTemplateDataProvider( $providerFromConfig );
		return $panelManager;
	},
	'BlueSpiceDiscoveryCookieHandler' => static function ( MediaWikiServices $services ) {
		$panelManager = new CookieHandler( new WebRequest );
		return $panelManager;
	},

	'BSAttentionIndicatorFactory' => static function ( MediaWikiServices $services ) {
		$registry = $services->getService( 'MWStakeManifestRegistryFactory' )
			->get( 'BlueSpiceDiscoveryAttentionIndicatorRegistry' );
		return new AttentionIndicatorFactory(
			$registry,
			$services->getMainConfig(),
			$services
		);
	},
	'BlueSpiceDiscoveryBreadcrumbDataProviderFactory' => static function ( MediaWikiServices $services ) {
		$context = RequestContext::getMain();
		$messageLocalizer = $context;
		return new BreadcrumbDataProviderFactory(
			$messageLocalizer,
			$context->getRequest()->getValues(),
			$services->getTitleFactory(),
			$services->getNamespaceInfo(),
			$services->getObjectFactory()
		);
	},
	'BlueSpiceDiscoverySkinSlotRenderer' => static function ( MediaWikiServices $services ) {
		$renderer = new SkinSlotRenderer(
			$services->getService( 'MWStakeCommonUISkinSlotRendererFactory' )
		);
		return $renderer;
	},
	'BlueSpiceDiscoveryComponentRenderer' => static function ( MediaWikiServices $services ) {
		$renderer = new ComponentRenderer(
			$services->getService( 'MWStakeCommonUIComponentManager' ),
			$services->getService( 'MWStakeCommonUIRendererDataTreeBuilder' ),
			$services->getService( 'MWStakeCommonUIRendererDataTreeRenderer' )
		);
		return $renderer;
	},
	'BlueSpiceDiscoveryMenuProviderFactory' => static function ( MediaWikiServices $services ) {
		return new MenuProviderFactory(
			$services->get( 'MWStakeManifestObjectFactory' )
		);
	},
	'BlueSpiceDiscoveryMenuManager' => static function ( MediaWikiServices $services ) {
		return new MenuManager(
			$services->get( 'BlueSpiceDiscoveryMenuProviderFactory' ),
			$services->getConfigFactory()
		);
	},
	'BlueSpiceDiscoveryLangLinksProviderFactory' => static function ( MediaWikiServices $services ) {
		return new LangLinksProviderFactory(
			$services->get( 'MWStakeManifestObjectFactory' ),
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},
	'BlueSpiceDiscoveryMetaItemFactory' => static function ( MediaWikiServices $services ) {
		return new MetaItemsProviderFactory(
			$services->get( 'MWStakeManifestObjectFactory' )
		);
	},
	'BlueSpiceDiscoveryMetaItemManager' => static function ( MediaWikiServices $services ) {
		return new MetaItemsManager(
			$services->get( 'BlueSpiceDiscoveryMetaItemFactory' ),
			$services->getConfigFactory()
		);
	},
	'BlueSpiceDiscoveryTemplateDataProviderFactory' => static function ( MediaWikiServices $services ) {
		return new TemplateDataProviderFactory(
			$services->get( 'MWStakeManifestObjectFactory' )
		);
	},
	'BlueSpiceDiscoveryBackLinkProviderFactory' => static function ( MediaWikiServices $services ) {
		return new BackLinkProviderFactory(
			$services->getObjectFactory()
		);
	}
];
