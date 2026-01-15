<?php

namespace BlueSpice\Discovery\Component;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleDropdownIcon;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleDropdownItemlistFromArray;
use MWStake\MediaWiki\Component\CommonUserInterface\LinkFormatter;

class ShareOptions extends SimpleDropdownIcon {

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
		return 'share-menu';
	}

	/**
	 * @return array
	 */
	public function getButtonClasses(): array {
		$classes = [ 'ico-btn' ];

		if ( !isset( $this->componentProcessData['panel'] )
			|| !isset( $this->componentProcessData['panel']['share'] )
			|| empty( $this->componentProcessData['panel']['share'] ) ) {
				array_push( $classes, 'disabled' );
		}

		return $classes;
	}

	/**
	 * @return array
	 */
	public function getMenuClasses(): array {
		return [ 'mws-dropdown-secondary' ];
	}

	/**
	 * @return array
	 */
	public function getIconClasses(): array {
		return [ 'ico-btn', 'bi-share' ];
	}

	/**
	 * @return Message
	 */
	public function getTitle(): Message {
		return Message::newFromKey( 'bs-discovery-sidebar-secondary-share-link-title' );
	}

	/**
	 * @return Message
	 */
	public function getAriaLabel(): Message {
		return Message::newFromKey( 'bs-discovery-sidebar-secondary-share-link-aria-label' );
	}

	/**
	 *
	 * @return array
	 */
	public function getRequiredRLModules(): array {
		return parent::getRequiredRLModules() + [ 'skin.discovery.shareoptions' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		if ( !isset( $this->componentProcessData['panel'] )
			|| !isset( $this->componentProcessData['panel']['share'] ) ) {
			return [];
		}
		$share = $this->componentProcessData['panel']['share'];
		if ( empty( $share ) ) {
			return [];
		}

		$services = MediaWikiServices::getInstance();
		/** @var LinkFormatter */
		$linkFormatter = $services->getService( 'MWStakeLinkFormatter' );

		return [
			new SimpleDropdownItemlistFromArray( [
				'id' => $this->getId() . '-list',
				'links' => $linkFormatter->formatLinks( $share ),
				'classes' => []
			] )
		];
	}
}
