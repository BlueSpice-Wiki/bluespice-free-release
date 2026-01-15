<?php

namespace MediaWiki\Extension\NotifyMe\Skin\Component;

use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\NotifyMe\Storage\WebNotificationQueryStore;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCard;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardBody;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardHeader;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleDropdownIcon;
use MWStake\MediaWiki\Component\CommonUserInterface\IRestrictedComponent;

class NotificationsMenu extends SimpleDropdownIcon implements IRestrictedComponent {

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
		return 'notifications-btn';
	}

	/**
	 * @inheritDoc
	 */
	public function getContainerClasses(): array {
		return [ 'has-megamenu' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getButtonClasses(): array {
		return [ 'ico-btn', 'notifications-megamenu-btn' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getMenuClasses(): array {
		return [ 'megamenu' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getIconClasses(): array {
		return [ 'bi-bell-fill' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getTitle(): Message {
		return Message::newFromKey( 'notifyme-navbar-button-title' );
	}

	/**
	 * @inheritDoc
	 */
	public function getAriaLabel(): Message {
		return Message::newFromKey( 'notifyme-navbar-button-aria-label' );
	}

	/**
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		return [
			new SimpleCard( [
				'id' => 'notifications-mm',
				'classes' => [
					'mega-menu', 'd-flex', 'justify-content-center'
				],
				'items' => [
					new SimpleCardBody( [
						'id' => 'notifications-megamn-body',
						'classes' => [ 'd-flex', 'mega-menu-wrapper', 'notifications-megamenu-body' ],
						'items' => [
							new SimpleCard( [
								'id' => 'notifications-card',
								'classes' => [ 'card-mn' ],
								'items' => [
									new SimpleCardHeader( [
										'id' => 'notifications-card-head',
										'classes' => [ 'menu-title' ],
										'items' => [
											new Literal(
												'notifications-menu-title',
												Message::newFromKey( 'notifyme-notifications-mega-menu-title' )
											)
										]
									] ),
									new Literal(
										'notifications-unread-notifications-count',
										$this->getUnreadNotificationsCount()
									)
								]
							] )
						]
					] )
				]
			] ),
			/* literal for transparent megamenu container */
			new Literal(
				'notifications-mm-div',
				'<div id="notifications-mm-div" class="mm-bg"></div>'
			)
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getPermissions(): array {
		return [ 'read' ];
	}

	/**
	 * @inheritDoc
	 */
	public function shouldRender( IContextSource $context ): bool {
		if ( $context->getUser()->isAnon() ) {
			return false;
		}

		return true;
	}

	/**
	 * @return string
	 */
	private function getUnreadNotificationsCount(): string {
		$user = RequestContext::getMain()->getUser();

		$services = MediaWikiServices::getInstance();

		/** @var WebNotificationQueryStore $webNotifyMe.Store */
		$notificationStore = $services->getService( 'NotifyMe.WebQueryStore' );

		// You might want to render the count only for the bell icon
		$count = $notificationStore->getTotalCount( 'pending', $user );

		return Html::hidden( 'unreadNotificationsCount', $count );
	}

	/**
	 * @inheritDoc
	 */
	public function getRequiredRLModules(): array {
		return [
			'ext.notifyme.megamenu'
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getRequiredRLStyles(): array {
		return [
			'ext.notifyme.megamenu.styles'
		];
	}
}
