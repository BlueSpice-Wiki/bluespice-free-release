<?php

namespace BlueSpice\Discovery\MenuProvider;

use BlueSpice\Discovery\Component\AsyncSubpageTreePanel;
use BlueSpice\Discovery\IMenuProvider;
use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;

class SubpageTree implements IMenuProvider {

	/**
	 * @return string
	 */
	public function getName(): string {
		return 'subpage-tree';
	}

	/**
	 * @return Message
	 */
	public function getLabelMsg(): Message {
		return new Message( 'bs-discovery-menu-provider-subpage-tree-label' );
	}

	/**
	 * @return Message
	 */
	public function getDescriptionMsg(): Message {
		return new RawMessage( 'bs-discovery-menu-provider-subpage-tree-desc' );
	}

	/**
	 * @return IComponent
	 */
	public function getComponent(): IComponent {
		return new AsyncSubpageTreePanel();
	}
}
