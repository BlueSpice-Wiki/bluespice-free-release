<?php

namespace BlueSpice\Discovery;

use BlueSpice\Discovery\SkinSlotRenderer\BreadcrumbSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\DataAfterContentSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\DataAfterTitleSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\DataBeforeContentSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\GlobalActionsAdministrationSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\GlobalActionsEditingSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\GlobalActionsOverviewSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\MainPanelSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\NavbarPrimaryItemsSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\NavbarPrimarySearchFormSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\SidebarPrimaryTabPanelSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\SidebarSecondaryTabPanelSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\TitleActionsSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\ToolbarPanelSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\ToolsAfterContentSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\UserMenuCardsSkinSlotRenderer;
use MediaWiki\Context\RequestContext;

class SkinSlots {

	public function __construct() {
	}

	/**
	 *
	 * @return void
	 */
	public function init(): void {
		// Skin slots
		$this->createSkinSlot(
			NavbarPrimaryItemsSkinSlotRenderer::REG_KEY,
			NavbarPrimaryItemsSkinSlotRenderer::class
		);
		$this->createSkinSlot(
			NavbarPrimarySearchFormSkinSlotRenderer::REG_KEY,
			NavbarPrimarySearchFormSkinSlotRenderer::class
		);
		$this->createSkinSlot(
			DataAfterContentSkinSlotRenderer::REG_KEY,
			DataAfterContentSkinSlotRenderer::class
		);
		$this->createSkinSlot(
			DataBeforeContentSkinSlotRenderer::REG_KEY,
			DataBeforeContentSkinSlotRenderer::class
		);
		$this->createSkinSlot(
			DataAfterTitleSkinSlotRenderer::REG_KEY,
			DataAfterTitleSkinSlotRenderer::class
		);
		$this->createSkinSlot(
			TitleActionsSkinSlotRenderer::REG_KEY,
			TitleActionsSkinSlotRenderer::class
		);
		$this->createSkinSlot(
			UserMenuCardsSkinSlotRenderer::REG_KEY,
			UserMenuCardsSkinSlotRenderer::class
		);
		$this->createSkinSlot(
			GlobalActionsOverviewSkinSlotRenderer::REG_KEY,
			GlobalActionsOverviewSkinSlotRenderer::class
		);
		$this->createSkinSlot(
			GlobalActionsEditingSkinSlotRenderer::REG_KEY,
			GlobalActionsEditingSkinSlotRenderer::class
		);
		$this->createSkinSlot(
			GlobalActionsAdministrationSkinSlotRenderer::REG_KEY,
			GlobalActionsAdministrationSkinSlotRenderer::class
		);
		$this->createSkinSlot(
			MainPanelSkinSlotRenderer::REG_KEY,
			MainPanelSkinSlotRenderer::class
		);
		$this->createSkinSlot(
			ToolbarPanelSkinSlotRenderer::REG_KEY,
			ToolbarPanelSkinSlotRenderer::class
		);
		$this->createSkinSlot(
			ToolsAfterContentSkinSlotRenderer::REG_KEY,
			ToolsAfterContentSkinSlotRenderer::class
		);
		$this->createSkinSlot(
			BreadcrumbSkinSlotRenderer::REG_KEY,
			BreadcrumbSkinSlotRenderer::class
		);
		$this->createSkinSlot(
			SidebarPrimaryTabPanelSkinSlotRenderer::REG_KEY,
			SidebarPrimaryTabPanelSkinSlotRenderer::class
		);
		$this->createSkinSlot(
			SidebarSecondaryTabPanelSkinSlotRenderer::REG_KEY,
			SidebarSecondaryTabPanelSkinSlotRenderer::class
		);
	}

	/**
	 *
	 * @param string $skinSlotRendererKey
	 * @param ISkinSlotRenderer $skinSlotRendererClass
	 */
	private function createSkinSlot( $skinSlotRendererKey, $skinSlotRendererClass ) {
		$requestContext = RequestContext::getMain();

		$GLOBALS['mwsgCommonUISkinSlots'][$skinSlotRendererKey] = [];
		$GLOBALS['mwsgCommonUISkinSlotRenderers'][$skinSlotRendererKey] = [
			'class' => $skinSlotRendererClass,
			'services' => [
				'MWStakeSkinSlotRegistry',
				'MWStakeCommonUIComponentManager',
				'MWStakeCommonUIRendererDataTreeBuilder',
				'MWStakeCommonUIRendererDataTreeRenderer',
				'MWStakeSkinSlotRegistry',
				'BlueSpiceDiscoveryCookieHandler',
				'PermissionManager'
			],
			'args' => [
				$skinSlotRendererKey,
				$requestContext
			]
		];
		$GLOBALS['mwsgCommonUISkinSlotsEnabled'][] = $skinSlotRendererKey;
	}
}
