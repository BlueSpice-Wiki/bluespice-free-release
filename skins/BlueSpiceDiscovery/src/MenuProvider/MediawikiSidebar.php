<?php

namespace BlueSpice\Discovery\MenuProvider;

use BlueSpice\Discovery\Component\MediaWikiLinksPanel;
use BlueSpice\Discovery\IMenuProvider;
use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;

class MediawikiSidebar implements IMenuProvider {

	/**
	 * @return string
	 */
	public function getName(): string {
		return 'mediawiki-sidebar';
	}

	/**
	 * @return Message
	 */
	public function getLabelMsg(): Message {
		return new Message( 'bs-discovery-menu-provider-mediawiki-sidebar-label' );
	}

	/**
	 * @return Message
	 */
	public function getDescriptionMsg(): Message {
		return new RawMessage( 'bs-discovery-menu-provider-mediawiki-sidebar-desc' );
	}

	/**
	 * @return IComponent
	 */
	public function getComponent(): IComponent {
		return new MediaWikiLinksPanel();
	}
}
