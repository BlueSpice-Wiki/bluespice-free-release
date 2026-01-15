<?php

namespace BlueSpice\Discovery\Component;

use MediaWiki\Context\IContextSource;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleLink;
use MWStake\MediaWiki\Component\CommonUserInterface\LinkFormatter;

class Watch extends SimpleLink {

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
		return $this->getAttributeData( 'id' );
	}

	/**
	 * @inheritDoc
	 */
	public function getClasses(): array {
		$class = 'bi-star';
		if ( $this->getId() === 'ca-unwatch' ) {
			$class = 'bi-star-fill';
		}
		return [ 'ico-btn', $class ];
	}

	/**
	 * @inheritDoc
	 */
	public function getRole(): string {
		return 'button';
	}

	/**
	 * @inheritDoc
	 */
	public function getTitle(): Message {
		$id = $this->getId();
		// give grep a chance
		// tooltip-ca-watch
		// tooltip-ca-unwatch
		return Message::newFromKey( 'tooltip-' . $id );
	}

	/**
	 * @inheritDoc
	 */
	public function getAriaLabel(): Message {
		return Message::newFromKey( 'bs-discovery-sidebar-secondary-watch-link-aria-label' );
	}

	/**
	 * @inheritDoc
	 */
	public function getHref(): string {
		return $this->getAttributeData( 'href' );
	}

	/**
	 * @inheritDoc
	 */
	public function getDataAttributes(): array {
		return $this->getAttributeData( 'data', [] );
	}

	/**
	 * @inheritDoc
	 */
	public function getRequiredRLModules(): array {
		return parent::getRequiredRLModules() + [ 'skin.discovery.watch.scripts' ];
	}

	/**
	 *
	 * @param string $key
	 * @param string $return
	 * @return array
	 */
	private function getAttributeData( $key, $return = '' ) {
		if ( empty( $this->componentProcessData['panel']['watch'] ) ) {
			return $return;
		}

		$services = MediaWikiServices::getInstance();
		/** @var LinkFormatter */
		$linkFormatter = $services->getService( 'MWStakeLinkFormatter' );
		$formattedData = $linkFormatter->formatLinks(
			$this->componentProcessData['panel']['watch']
		);
		return empty( $formattedData[0][$key] ) ? $return : $formattedData[0][$key];
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool {
		// `Title::isWatchable` was deprecated in 1.37 and removed in 1.38
		// See https://github.com/wikimedia/mediawiki/blob/REL1_35/includes/Title.php#L1222-L1234
		$title = $context->getTitle();
		if ( $title === null ) {
			return false;
		}
		$user = $context->getUser();
		$nsInfo = MediaWikiServices::getInstance()->getNamespaceInfo();
		return $title->getText() !== '' && !$title->isExternal() &&
			$nsInfo->isWatchable( $title->getNamespace() ) &&
			!$user->isAnon();
	}

}
