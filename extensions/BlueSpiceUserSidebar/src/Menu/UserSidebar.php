<?php

namespace BlueSpice\UserSidebar\Menu;

use BlueSpice\UserSidebar\WidgetFactory;
use MediaWiki\Extension\MenuEditor\Menu\MediawikiSidebar;
use MediaWiki\Title\Title;
use MWStake\MediaWiki\Component\Wikitext\ParserFactory;

class UserSidebar extends MediawikiSidebar {
	/** @var WidgetFactory */
	private $widgetFactory;

	/**
	 * @param WidgetFactory $widgetFactory
	 * @param ParserFactory $parserFactory
	 */
	public function __construct( WidgetFactory $widgetFactory, ParserFactory $parserFactory ) {
		parent::__construct( $parserFactory );
		$this->widgetFactory = $widgetFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function getJSClassname(): string {
		return 'ext.usersidebar.menu.UserSidebarTree';
	}

	/**
	 * @inheritDoc
	 */
	public function getRLModule(): string {
		return 'ext.blueSpice.userSidebar.menu';
	}

	/**
	 * @inheritDoc
	 */
	public function appliesToTitle( Title $title ): bool {
		if ( !$title->getNamespace() === NS_USER ) {
			return false;
		}
		$page = $title->getDBkey();
		return substr( $page, -8 ) === '/Sidebar';
	}

	/**
	 * @inheritDoc
	 */
	public function getKey(): string {
		return 'user-sidebar';
	}

	/**
	 * @return string[]
	 */
	public function getAllowedNodes(): array {
		return [ 'menu-user-sidebar-keyword', 'menu-raw-text', 'menu-two-fold-link-spec' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getEmptyContent(): array {
		$nodes = [];
		foreach ( $this->widgetFactory->getAllKeys() as $key ) {
			$nodes[] = new UserSideBarKeyword( 1, $key );
		}
		return $nodes;
	}

	/**
	 * @inheritDoc
	 */
	public function getEditRight(): string {
		return 'edit';
	}
}
