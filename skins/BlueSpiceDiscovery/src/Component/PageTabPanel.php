<?php

namespace BlueSpice\Discovery\Component;

use MediaWiki\Context\IContextSource;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\ComponentBase;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;
use MWStake\MediaWiki\Component\CommonUserInterface\IRestrictedComponent;
use MWStake\MediaWiki\Component\CommonUserInterface\ITabPanel;

class PageTabPanel extends ComponentBase implements ITabPanel, IRestrictedComponent {

	/**
	 *
	 * @return string
	 */
	public function getId(): string {
		return 'tp-page';
	}

	/**
	 *
	 * @return Message
	 */
	public function getText(): Message {
		return Message::newFromKey( 'bs-discovery-tabpanel-page-text' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getTitle(): Message {
		return Message::newFromKey( 'bs-discovery-tabpanel-page-title' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getAriaLabel(): Message {
		return Message::newFromKey( 'bs-discovery-tabpanel-page-aria-label' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getAriaDesc(): Message {
		return Message::newFromKey( 'bs-discovery-tabpanel-page-desc' );
	}

	/**
	 *
	 * @return IComponent[]
	 */
	public function getSubComponents(): array {
		return [
			new ActionsPanel(),
			new ToolbarPanel(),
			new DetailsPanel(),
		];
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function isActive( $context ): bool {
		return true;
	}

	/**
	 *
	 * @return array
	 */
	public function getPermissions(): array {
		return [ 'read' ];
	}
}
