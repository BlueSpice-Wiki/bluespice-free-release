<?php

namespace BlueSpice\Discovery\Component;

use BlueSpice\Discovery\SkinSlotRenderer\MainPanelSkinSlotRenderer;
use MediaWiki\Context\IContextSource;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\CallbackLiteral;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCard;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardHeader;
use MWStake\MediaWiki\Component\CommonUserInterface\SkinSlotRegistry;

class MainLinksPanel extends SimpleCard {

	/**
	 * @var MediaWikiServices
	 */
	private $services;

	/**
	 *
	 */
	public function __construct() {
		$this->services = MediaWikiServices::getInstance();

		parent::__construct( [] );
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return 'main-pnl';
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
		$item = new SimpleCard( [
			'id' => 'main-links-panel',
			'classes' => [ 'w-100', 'bg-transp' ],
			'items' => [
				new SimpleCardHeader( [
					'id' => 'main-links-panel-head',
					'classes' => [ 'menu-title' ],
					'items' => [
						new Literal(
							'main-links-panel-head',
							Message::newFromKey( 'bs-discovery-main-links-heading' )->text()
						)
					]
				] ),
				new CallbackLiteral(
					$this->getId() . '-list',
					function ( $componetProcessData ) {
						return $this->getSkinSlotHtml();
					}
				)
			]
		] );
		return [ $item ];
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool {
		/** @var SkinSlotRegistry */
		$skinSlotRegistry = $this->services->get( 'MWStakeSkinSlotRegistry' );
		$skinSlot = $skinSlotRegistry->getSkinSlot( MainPanelSkinSlotRenderer::REG_KEY );
		if ( empty( $skinSlot ) ) {
			return false;
		}
		return true;
	}

	/**
	 *
	 * @return string
	 */
	private function getSkinSlotHtml(): string {
		/** @var SkinSlotRendererFactory */
		$skinSlotRendererFactory = $this->services->get( 'MWStakeCommonUISkinSlotRendererFactory' );

		/** @var MainPanelSkinSlotRenderer */
		$skinSlotRenderer = $skinSlotRendererFactory->create( MainPanelSkinSlotRenderer::REG_KEY );

		return $skinSlotRenderer->getHtml();
	}
}
