<?php

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENT_COMMONUSERINTERFACE_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENT_COMMONUSERINTERFACE_VERSION', '6.0.0' );

MWStake\MediaWiki\ComponentLoader\Bootstrapper::getInstance()
->register( 'commonuserinterface', static function () {
	/**
	 * Allows to register additional component interfaces
	 */
	$GLOBALS['mwsgCommonUIComponentRegistry'] = [
		'literal' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\ILiteral',
		'message-literal' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\IMessageLiteral',
		'button' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\IButton',
		'panel' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\IPanel',
		'dropdown' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\IDropdown',
		'dropdown-button' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\IDropdownButton',
		'dropdown-icon' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\IDropdownIcon',
		'dropdown-icon-split-button' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\IDropdownIconSplitButton',
		'dropdown-split-button' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\IDropdownSplitButton',
		'dropdown-split-link' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\IDropdownSplitLink',
		'dropdown-itemlist' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\IDropdownItemlist',
		'dropdown-itemlist-item' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\IDropdownItemlistItem',
		'dropdown-itemlist-from-array' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\IDropdownItemlistFromArray',
		'separator' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\ISeparator',
		'card' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\ICard',
		'card-header' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\ICardHeader',
		'card-title' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\ICardTitle',
		'card-subtitle' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\ICardSubTitle',
		'card-body' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\ICardBody',
		'card-text' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\ICardText',
		'card-footer' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\ICardFooter',
		'card-image' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\ICardImage',
		'card-link' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\ICardLink',
		'collapsible-card' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\ICollapsibleCard',
		'linklistgroup' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\ILinklistGroup',
		'linklistgroup-item' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\ILinklistGroupItem',
		'linklistgroup-from-array' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\ILinklistGroupFromArray',
		'link' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\ILink',
		'text-link' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\ITextLink',
		'badge' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\IBadge',
		'button-group' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\IButtonGroup',
		'media-object' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\IMediaObject',
		'tree-container' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\ITreeContainer',
		'tree-text-node' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\ITreeTextNode',
		'tree-link-node' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\ITreeLinkNode',
		'container' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\IContainer',
	];

	/**
	 * Allows to register `IComponentRenderer` objects using any `ObjectFactory` compatible spec.
	 * If the value is just a string a `ObjectFactory` with the `class` key will be created implicitly.
	 */
	$GLOBALS['mwsgCommonUIComponentRendererRegistry'] = [
		'*' => [
			'literal' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\Literal',
			'message-literal' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\MessageLiteral',
			'tree-container' => [
				'class' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\TreeContainer',
			],
			'tree-text-node' => [
				'class' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\TreeTextNode',
			],
			'tree-link-node' => [
				'class' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\TreeLinkNode',
				'services' => [ 'MainConfig' ]
			],
			'container' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\Container',
		],
		'bootstrap5' => [
			'button' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\Button',
			'panel' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\Card',
			'dropdown' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\Dropdown',
			'dropdown-button' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\DropdownButton',
			'dropdown-icon' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\DropdownIcon',
			'dropdown-icon-split-button' => [
				'class' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\DropdownIconSplitButton',
				'services' => [ 'MainConfig' ]
			],
			'dropdown-split-button' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\DropdownSplitButton',
			'dropdown-split-link' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\DropdownSplitLink',
			'dropdown-itemlist' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\DropdownItemlist',
			'dropdown-itemlist-item' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\DropdownItemlistItem',
			'dropdown-itemlist-from-array' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\DropdownItemlistFromArray',
			'separator' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\Separator',
			'card' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\Card',
			'card-header' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\CardHeader',
			'card-title' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\CardTitle',
			'card-subtitle' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\CardSubTitle',
			'card-body' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\CardBody',
			'card-text' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\CardText',
			'card-footer' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\CardFooter',
			'card-image' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\CardImage',
			'card-link' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\CardLink',
			'collapsible-card' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\CollapsibleCard',
			'linklistgroup' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\LinklistGroup',
			'linklistgroup-item' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\LinklistGroupItem',
			'linklistgroup-from-array' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\LinklistGroupFromArray',
			'link' => [
				'class' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\Link',
				'services' => [ 'MainConfig' ]
			],
			'text-link' => [
				'class' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\TextLink',
				'services' => [ 'MainConfig' ]
			],
			'badge' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\Badge',
			'button-group' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\ButtonGroup',
			'media-object' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Renderer\\MediaObject',
		]
	];

	$GLOBALS['mwsgCommonUIComponentRendererType'] = 'bootstrap5';

	$GLOBALS['mwsgCommonUIComponentTreeNodeRegistry'] = [
		'tree-text-node' => [
			'class' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Component\\SimpleTreeTextNode',
		],
		'tree-link-node' => [
			'class' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Component\\SimpleTreeLinkNode',
		],
	];

	$GLOBALS['mwsgCommonUISkinSlots'] = [
		'siteNoticeAfter' => [],
		'skinAfterContent' => []
	];

	/**
	 * Allows to register `ISkinSlotRenderer` objects using any `ObjectFactory` compatible spec
	 */
	$GLOBALS['mwsgCommonUISkinSlotRenderers'] = [
		// Use internal fallback to GenericSkinSlotRenderer by giving empty spec array
		'siteNoticeAfter' => [],
		'skinAfterContent' => [],
	];

	/** Allows to add component filter for rendering components */
	$GLOBALS['mwsgCommonUIComponentFilters'] = [
		'default' => [
			'class' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\ComponentFilter\\DefaultFilter'
		]
	];

	$GLOBALS['mwsgCommonUISkinSlotsEnabled'] = [ 'siteNoticeAfter', 'skinAfterContent' ];

	$GLOBALS['wgServiceWiringFiles'][] = __DIR__ . '/includes/ServiceWiring.php';

	$GLOBALS['wgExtensionFunctions'][] = static function () {
		$hookContainer = \MediaWiki\MediaWikiServices::getInstance()->getHookContainer();
		$runner = new \MWStake\MediaWiki\Component\CommonUserInterface\Setup();
		$hookContainer->register( 'SiteNoticeAfter', [ $runner, 'onSiteNoticeAfter' ] );
		$hookContainer->register( 'BeforePageDisplay', [ $runner, 'onBeforePageDisplay' ] );
		$hookContainer->register( 'SkinAfterContent', [ $runner, 'onSkinAfterContent' ] );
	};

	$GLOBALS['wgResourceModules']['mwstake.component.commonui.tree-component'] = [
		'localBasePath' => __DIR__ . "/resources/tree/",
		'packageFiles' => [
			'tree.js',
		],
	];

	$GLOBALS['wgResourceModules']['mwstake.component.commonui.tree-component.styles'] = [
		'localBasePath' => __DIR__ . "/resources/tree/",
		'styles' => [
			'tree.css',
		],
	];

	// phpcs:disable
	// Not yet ready
	#$GLOBALS['wgResourceModules']['mwstake.component.commonui'] = [
	#	'localBasePath' => __DIR__ . "/resources/",
	#	'packageFiles' => [
	#		'init.js',
	#		# 'commonui/renderer.js',
	#		[
	#			'name' => 'renderer.json',
	#			// phpcs:ignore
	#			'callback' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Setup::makeClientSideRendererConfig'
	#		],
	#	],
	#];
	#$GLOBALS['wgResourceModules']['mwstake.component.commonui.componentrenderer'] = [
		// phpcs:ignore
	#	'class' => 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\ResourceLoader\\ComponentRenderer'
	#];

	#$GLOBALS['wgMessagesDirs']['mwstake-component-commonui'] = __DIR__ . '/i18n';

	#$GLOBALS['wgAPIModules']['mwstake-commonui-skinslot']
	#	= 'MWStake\\MediaWiki\\Component\\CommonUserInterface\\Api\\SkinSlot';
	// phpcs:enable
} );
