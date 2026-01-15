<?php

namespace BlueSpice\Discovery\Component;

use BlueSpice\Discovery\SkinSlotRenderer\ToolbarPanelSkinSlotRenderer;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\CallbackLiteral;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCard;
use MWStake\MediaWiki\Component\CommonUserInterface\IRestrictedComponent;

class ToolbarPanel extends SimpleCard implements IRestrictedComponent {

	/**
	 *
	 */
	public function __construct() {
		parent::__construct( [] );
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return 'toolbar';
	}

	/**
	 * @inheritDoc
	 */
	public function getContainerClasses(): array {
		return [ 'w-100', 'bg-transp' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		$componentProcessData = $this->componentProcessData;
		return [
			new CallbackLiteral(
				$this->getId() . '-list',
				function ( $componetProcessData ) {
					return $this->getSkinSlotHtml();
				}
			),
		];
	}

	/**
	 *
	 * @return string[]
	 */
	public function getPermissions(): array {
		return [ 'read' ];
	}

	/**
	 *
	 * @return string
	 */
	private function getSkinSlotHtml(): string {
		/** @var MediaWikiServices */
		$services = MediaWikiServices::getInstance();

		/** @var SkinSlotRendererFactory */
		$skinSlotRendererFactory = $services->get( 'MWStakeCommonUISkinSlotRendererFactory' );

		/** @var ExtendedSkinSlotRendererBase */
		$skinSlotRenderer = $skinSlotRendererFactory->create( ToolbarPanelSkinSlotRenderer::REG_KEY );

		return $skinSlotRenderer->getHtml( $this->componentProcessData );
	}
}
