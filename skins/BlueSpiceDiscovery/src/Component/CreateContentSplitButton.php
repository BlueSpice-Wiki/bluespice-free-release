<?php

namespace BlueSpice\Discovery\Component;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleDropdownIconSplitButton;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleDropdownItemlistFromArray;

class CreateContentSplitButton extends SimpleDropdownIconSplitButton {

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
		return 'new-content';
	}

	/**
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		return [
			new SimpleDropdownItemlistFromArray( [
				'id' => 'new-content-itms',
				'classes' => [],
				'links' => $this->getMenuLinks()
			] )
		];
	}

	/**
	 * @return array
	 */
	public function getContainerClasses(): array {
		return [ 'mx-2', 'new-page-split-btn' ];
	}

	/**
	 * @return array
	 */
	public function getButtonClasses(): array {
		return [ 'ico-btn', 'new-page-btn' ];
	}

		/**
		 * @return array
		 */
	public function getSplitButtonClasses(): array {
		return [ 'new-page-menu-btn' ];
	}

	/**
	 * @return array
	 */
	public function getMenuClasses(): array {
		return [ 'mws-dropdown-secondary', 'dropdown-menu-end' ];
	}

	/**
	 * @return Message
	 */
	public function getIconClasses(): array {
		return [ 'bi-plus-lg' ];
	}

	/**
	 * @return Message
	 */
	public function getButtonTitle(): Message {
		return Message::newFromKey( 'bs-discovery-header-create-button-title' );
	}

	/**
	 * @return Message
	 */
	public function getSplitButtonTitle(): Message {
		return Message::newFromKey( 'bs-discovery-header-create-split-button-title' );
	}

	/**
	 * @return Message
	 */
	public function getButtonAriaLabel(): Message {
		return Message::newFromKey( 'bs-discovery-header-create-button-aria-label' );
	}

	/**
	 * @return Message
	 */
	public function getSplitButtonAriaLabel(): Message {
		return Message::newFromKey( 'bs-discovery-header-create-split-button-aria-label' );
	}

	/**
	 *
	 * @return bool
	 */
	public function buttonIsDisabled(): bool {
		if ( !isset( $this->componentProcessData['panel'] )
			|| !isset( $this->componentProcessData['panel']['create'] )
			|| !isset( $this->componentProcessData['panel']['create']['ca-new-page'] )
			|| empty( $this->componentProcessData['panel']['create']['ca-new-page'] ) ) {
				return true;
		}
		return false;
	}

		/**
		 *
		 * @return bool
		 */
	public function splitButtonIsDisabled(): bool {
		if ( !isset( $this->componentProcessData['panel'] )
			|| !isset( $this->componentProcessData['panel']['create'] )
			|| empty( $this->componentProcessData['panel']['create'] ) ) {
				return true;
		}
		return false;
	}

	/**
	 *
	 * @return array
	 */
	private function getMenuLinks(): array {
		$items = [];

		if ( !isset( $this->componentProcessData['panel'] )
			|| !isset( $this->componentProcessData['panel']['create'] ) ) {
				return $items;
		}
		$create = $this->componentProcessData['panel']['create'];
		if ( empty( $create ) ) {
			return $items;
		}

		$services = MediaWikiServices::getInstance();
		/** @var LinkFormatter */
		$linkFormatter = $services->getService( 'MWStakeLinkFormatter' );
		$items = $linkFormatter->formatLinks( $create );

		return $items;
	}

}
