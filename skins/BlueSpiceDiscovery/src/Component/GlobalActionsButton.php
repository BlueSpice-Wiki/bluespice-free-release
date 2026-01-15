<?php

namespace BlueSpice\Discovery\Component;

use BlueSpice\Discovery\SkinSlotRenderer\GlobalActionsAdministrationSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\GlobalActionsEditingSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\GlobalActionsOverviewSkinSlotRenderer;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCard;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardBody;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardHeader;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleDropdownIcon;
use MWStake\MediaWiki\Component\CommonUserInterface\IRestrictedComponent;

class GlobalActionsButton extends SimpleDropdownIcon implements IRestrictedComponent {

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
		return 'ga-btn';
	}

	/**
	 * @return array
	 */
	public function getContainerClasses(): array {
		return [ 'has-megamenu' ];
	}

	/**
	 * @return array
	 */
	public function getButtonClasses(): array {
		return [ 'ico-btn' ];
	}

	/**
	 * @return array
	 */
	public function getMenuClasses(): array {
		return [ 'megamenu' ];
	}

	/**
	 * @return array
	 */
	public function getIconClasses(): array {
		return [ 'bi-gear-fill' ];
	}

	/**
	 * @return Message
	 */
	public function getTitle(): Message {
		return Message::newFromKey( 'bs-discovery-navbar-global-actions-button-title' );
	}

	/**
	 * @return Message
	 */
	public function getAriaLabel(): Message {
		return Message::newFromKey( 'bs-discovery-navbar-global-actions-button-aria-label' );
	}

	/**
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		$overviewCard = new SimpleCard( [
			'id' => 'ga-overview',
			'classes' => [ 'card-mn' ],
			'items' => [
				new SimpleCardHeader( [
					'id' => 'ga-menu-overview-head',
					'classes' => [ 'menu-title' ],
					'items' => [
						new Literal(
							'ga-menu-title',
							Message::newFromKey(
								'bs-discovery-navbar-global-actions-overview-text'
							)
						)
					]
				] ),
				new Literal(
					'ga-menu-list-items',
					$this->getOverviewSkinSlotHtml()
				)
			]
		] );

		$cardBodyItems = [ $overviewCard ];

		// Add editing card if content exists
		$editingHtml = $this->getEditingSkinSlotHtml();
		if ( $editingHtml ) {
			$editingCard = new SimpleCard( [
				'id' => 'ga-editing',
				'classes' => [ 'card-mn' ],
				'items' => [
					new SimpleCardHeader( [
						'id' => 'ga-menu-editing-head',
						'classes' => [ 'menu-title' ],
						'items' => [
							new Literal(
								'ga-menu-title',
								Message::newFromKey(
									'bs-discovery-navbar-global-actions-editing-text'
								)
							)
						]
					] ),
					new Literal(
						'ga-menu-list-items',
						$this->getEditingSkinSlotHtml()
					)
				]
			] );

			$cardBodyItems[] = $editingCard;
		}

		// Add administration card if content exists
		$administrationHtml = $this->getAdministrationSkinSlotHtml();
		if ( $administrationHtml ) {
			$administrationCard = new SimpleCard( [
				'id' => 'ga-administration',
				'classes' => [ 'card-mn' ],
				'items' => [
					new SimpleCardHeader( [
						'id' => 'ga-menu-administration-head',
						'classes' => [ 'menu-title' ],
						'items' => [
							new Literal(
								'ga-menu-title',
								Message::newFromKey(
									'bs-discovery-navbar-global-actions-administration-text'
								)
							)
						]
					] ),
					new Literal(
						'ga-menu-list-items',
						$administrationHtml
					)
				]
			] );

			$cardBodyItems[] = $administrationCard;
		}

		$mainCard = new SimpleCard( [
			'id' => 'ga-mm',
			'classes' => [ 'mega-menu', 'd-flex', 'justify-content-center' ],
			'items' => [
				new SimpleCardBody( [
					'id' => 'ga-tools-megamn-body',
					'classes' => [ 'd-flex', 'mega-menu-wrapper' ],
					'items' => $cardBodyItems
				] )
			]
		] );

		return [
			$mainCard,
			// literal for transparent megamenu container
			new Literal(
				'ga-mm-div',
				'<div id="ga-mm-div" class="mm-bg"></div>'
			)
		];
	}

	/**
	 *
	 * @return array
	 */
	public function getPermissions(): array {
		return [ 'read' ];
	}

	/**
	 *
	 * @return string
	 */
	private function getOverviewSkinSlotHtml(): string {
		/** @var MediaWikiServices */
		$services = MediaWikiServices::getInstance();

		/** @var SkinSlotRendererFactory */
		$skinSlotRendererFactory = $services->get( 'MWStakeCommonUISkinSlotRendererFactory' );

		/** @var GlobalActionsOverviewSkinSlotRenderer */
		$skinSlotRenderer = $skinSlotRendererFactory->create( GlobalActionsOverviewSkinSlotRenderer::REG_KEY );

		return $skinSlotRenderer->getHtml();
	}

	/**
	 *
	 * @return string
	 */
	private function getEditingSkinSlotHtml(): string {
		/** @var MediaWikiServices */
		$services = MediaWikiServices::getInstance();

		/** @var SkinSlotRendererFactory */
		$skinSlotRendererFactory = $services->get( 'MWStakeCommonUISkinSlotRendererFactory' );

		/** @var GlobalActionsEditingSkinSlotRenderer */
		$skinSlotRenderer = $skinSlotRendererFactory->create( GlobalActionsEditingSkinSlotRenderer::REG_KEY );

		return $skinSlotRenderer->getHtml();
	}

	/**
	 *
	 * @return string
	 */
	private function getAdministrationSkinSlotHtml(): string {
		/** @var MediaWikiServices */
		$services = MediaWikiServices::getInstance();

		/** @var SkinSlotRendererFactory */
		$skinSlotRendererFactory = $services->get( 'MWStakeCommonUISkinSlotRendererFactory' );

		/** @var GlobalActionsAdministrationSkinSlotRenderer */
		$skinSlotRenderer = $skinSlotRendererFactory->create( GlobalActionsAdministrationSkinSlotRenderer::REG_KEY );

		return $skinSlotRenderer->getHtml();
	}
}
