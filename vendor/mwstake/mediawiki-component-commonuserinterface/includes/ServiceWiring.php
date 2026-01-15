<?php

use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\CommonUserInterface\ComponentFilterFactory;
use MWStake\MediaWiki\Component\CommonUserInterface\ComponentManager;
use MWStake\MediaWiki\Component\CommonUserInterface\ComponentRendererFactory;
use MWStake\MediaWiki\Component\CommonUserInterface\GenericComponentRenderer;
use MWStake\MediaWiki\Component\CommonUserInterface\HtmlIdRegistry;
use MWStake\MediaWiki\Component\CommonUserInterface\LinkFormatter;
use MWStake\MediaWiki\Component\CommonUserInterface\RendererDataTreeBuilder;
use MWStake\MediaWiki\Component\CommonUserInterface\RendererDataTreeRenderer;
use MWStake\MediaWiki\Component\CommonUserInterface\SkinSlotRegistry;
use MWStake\MediaWiki\Component\CommonUserInterface\SkinSlotRendererFactory;
use MWStake\MediaWiki\Component\CommonUserInterface\TreeDataGenerator;

return [
	'MWStakeCommonUISkinSlotRendererFactory' => static function ( MediaWikiServices $services ) {
		return new SkinSlotRendererFactory(
			$GLOBALS['mwsgCommonUISkinSlotRenderers'],
			$services->getObjectFactory()
		);
	},

	'MWStakeCommonUIComponentManager' => static function ( MediaWikiServices $services ) {
		$componentManager = ComponentManager::singleton(
			RequestContext::getMain(),
			$GLOBALS['mwsgCommonUISkinSlots'],
			$GLOBALS['mwsgCommonUISkinSlotsEnabled'],
			$services->get( 'MWStakeCommonUIComponentFilterFactory' ),
			$services->getObjectFactory(),
			$services->getHookContainer()
		);

		return $componentManager;
	},

	'MWStakeCommonUIComponentRendererFactory' => static function ( MediaWikiServices $services ) {
		return new ComponentRendererFactory(
			$GLOBALS['mwsgCommonUIComponentRendererRegistry'],
			$GLOBALS['mwsgCommonUIComponentRegistry'],
			$GLOBALS['mwsgCommonUIComponentRendererType'],
			$services->getObjectFactory()
		);
	},

	'MWStakeCommonUIRendererDataTreeBuilder' => static function ( MediaWikiServices $services ) {
		return new RendererDataTreeBuilder(
			$services->getService( 'MWStakeCommonUIComponentRendererFactory' )
		);
	},

	'MWStakeCommonUIRendererDataTreeRenderer' => static function ( MediaWikiServices $services ) {
		return new RendererDataTreeRenderer(
			$services->getService( 'MWStakeCommonUIComponentRendererFactory' )
		);
	},

	'MWStakeCommonUIHtmlIdRegistry' => static function ( MediaWikiServices $services ) {
		$registry = HtmlIdRegistry::singleton();
		return $registry;
	},

	'MWStakeCommonUIGenericComponentRenderer' => static function ( MediaWikiServices $services ) {
		$renderer = new GenericComponentRenderer(
			$services->get( 'MWStakeCommonUIComponentManager' ),
			$services->get( 'MWStakeCommonUIRendererDataTreeBuilder' ),
			$services->get( 'MWStakeCommonUIRendererDataTreeRenderer' )
		);
		return $renderer;
	},

	'MWStakeSkinSlotRegistry' => static function ( MediaWikiServices $services ) {
		$skinSlotRegistry = SkinSlotRegistry::singleton(
			$GLOBALS['mwsgCommonUISkinSlots']
		);
		return $skinSlotRegistry;
	},

	'MWStakeLinkFormatter' => static function ( MediaWikiServices $services ) {
		$linkFormatter = new LinkFormatter(
			$services->getMainConfig()->get( 'ExternalLinkTarget' ),
			$services->getMainConfig()->get( 'NoFollowLinks' )
		);
		return $linkFormatter;
	},

	'MWStakeCommonUITreeDataGenerator' => static function ( MediaWikiServices $services ) {
		return new TreeDataGenerator(
			$GLOBALS['mwsgCommonUIComponentTreeNodeRegistry'],
			$services->getObjectFactory()
		);
	},

	'MWStakeCommonUIComponentFilterFactory' => static function ( MediaWikiServices $services ) {
		return new ComponentFilterFactory(
			$GLOBALS['mwsgCommonUIComponentFilters'],
			$services->getObjectFactory()
		);
	},
];
