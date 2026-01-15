<?php

namespace BlueSpice\Discovery\Component;

use BlueSpice\Discovery\SkinSlotRenderer\UserMenuCardsSkinSlotRenderer;
use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\Html\Html;
use MediaWiki\Language\RawMessage;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\User\User;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCard;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardBody;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardHeader;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleDropdown;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleLinklistGroupFromArray;
use MWStake\MediaWiki\Component\CommonUserInterface\LinkFormatter;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\DynamicFileDispatcherFactory;

class UserButtonMenu extends SimpleDropdown {

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
		return 'usr-btn';
	}

	/**
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		$services = MediaWikiServices::getInstance();
		/** @var LinkFormatter */
		$linkFormatter = $services->getService( 'MWStakeLinkFormatter' );
		$links = [];
		foreach ( $this->componentProcessData['template']['personal_urls'] as $key => $link ) {
			if ( in_array( $key, $this->getSkipList() ) ) {
				continue;
			}
			$links[$key] = $link;
			if ( !isset( $this->getOverrides()[$key] ) ) {
				continue;
			}
			foreach ( $this->getOverrides()[$key] as $override => $value ) {
				$links[$key][$override] = $value;
			}
		}

		$id = 'p-tools';

		return [
			new SimpleCard( [
				'id' => 'p-tools-megamn',
				'classes' => [
					'mega-menu', 'd-flex', 'justify-content-center'
				],
				'items' => [
					new SimpleCardBody( [
						'id' => 'p-tools-megamn-body',
						'classes' => [ 'd-flex', 'mega-menu-wrapper' ],
						'items' => [
							new Literal(
								'user-menu-additional-cards-unset',
								$this->getSkinSlotHtml()
							),
							new SimpleCard( [
								'id' => $id . '-menu',
								'classes' => [ 'card-mn' ],
								'items' => [
									new SimpleCardHeader( [
										'id' => $id . '-head',
										'classes' => [ 'menu-title' ],
										'items' => [
											new Literal(
												$id . '-title',
												Message::newFromKey(
													'bs-discovery-navbar-user-button-personal-menu-text'
												)->text()
											)
										]
									] ),
									new SimpleLinklistGroupFromArray( [
										'id' => $id,
										'classes' => [ 'menu-card-body', 'menu-list', 'll-dft' ],
										'links' => $linkFormatter->formatLinks( $this->sortLinks( $links ) ),
										'role' => 'group',
										'item-role' => 'presentation',
										'aria' => [
											'labelledby' => $id . '-head'
										],
									] ),
								]
							] )
						]
					] )
				]
			] ),
			/* literal for transparent megamenu container */
			new Literal(
				'usr-btn-menu-div',
				'<div id="usr-btn-menu-div" class="mm-bg"></div>'
			)
		];
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
	 * @return Message
	 */
	public function getText(): Message {
		return new RawMessage( $this->getAvatar() );
	}

	/**
	 * @return Message
	 */
	public function getTitle(): Message {
		return Message::newFromKey( 'bs-discovery-navbar-user-button-title' );
	}

	/**
	 * @return Message
	 */
	public function getAriaLabel(): Message {
		return Message::newFromKey( 'bs-discovery-navbar-user-button-aria-label' );
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool {
		/** @var User */
		$user = $context->getUser();
		if ( !$user->isAnon() ) {
			return true;
		}

		return false;
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

		/** @var UserMenuCardsSkinSlotRenderer */
		$skinSlotRenderer = $skinSlotRendererFactory->create( UserMenuCardsSkinSlotRenderer::REG_KEY );

		return $skinSlotRenderer->getHtml();
	}

	/**
	 * @return array
	 */
	protected function getFavoritePositions(): array {
		return [
			'watchlist' => 30,
			'mycontris' => 60,
			'mytalk' => 90,
			'userpage' => 100,
			'preferences' => 140,
			'logout' => 150,
		];
	}

	/**
	 * @return array
	 */
	protected function getSkipList(): array {
		return [
			'mytalk',
		];
	}

	/**
	 * @return array
	 */
	protected function getOverrides(): array {
		return [
			'mycontris' => [
				'text' => Message::newFromKey(
					'bs-discovery-personalurl-mycontributions-text'
				)
			],
			'userpage' => [
				'text' => Message::newFromKey(
					'bs-discovery-personalurl-userpage-text'
				)
			]
		];
	}

	/**
	 * @param array $links
	 * @return array
	 */
	protected function sortLinks( $links ): array {
		foreach ( $links as $key => &$data ) {
			if ( isset( $data['position'] ) ) {
				continue;
			}
			$data['position'] = isset( $this->getFavoritePositions()[$key] )
				? $this->getFavoritePositions()[$key]
				: 0;
		}
		usort( $links, static function ( $e1, $e2 ) {
			if ( $e1['position'] == $e2['position'] ) {
				return 0;
			}
			return $e1['position'] > $e2['position'] ? 1 : 0;
		} );
		return $links;
	}

	/**
	 *
	 * @return string
	 */
	private function getAvatar(): string {
		/** @var IContextSource */
		$context = RequestContext::getMain();

		/** @var User */
		$user = $context->getUser();

		$username = $user->getName();
		if ( MediaWikiServices::getInstance()->hasService( 'BSUtilityFactory' ) ) {
			$username = MediaWikiServices::getInstance()->getService(
				'BSUtilityFactory'
			)->getUserHelper( $user )->getDisplayName();
		}

		$imgBaseSize = 28;
		$realsize = $imgBaseSize * 1.4;
		$userImageParams = [
			'username' => $user->getName(),
			'height' => $realsize,
			'width' => $realsize,
		];
		/** @var DynamicFileDispatcherFactory $dynamicFileFactory */
		$dynamicFileFactory = MediaWikiServices::getInstance()->getService(
			'MWStake.DynamicFileDispatcher.Factory'
		);
		$src = $dynamicFileFactory->getUrl( 'userprofileimage', $userImageParams );

		return Html::element(
			'img',
			[
				// title is not required becaus the anchor tag arround this image hase already a title
				'src' => $src,
				'alt' => $username,
				'width' => $imgBaseSize . 'px',
				'height' => $imgBaseSize . 'px'
			]
		);
	}
}
