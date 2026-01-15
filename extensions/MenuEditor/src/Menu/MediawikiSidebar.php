<?php

namespace MediaWiki\Extension\MenuEditor\Menu;

use MediaWiki\Extension\MenuEditor\EditPermissionProvider;
use MediaWiki\Extension\MenuEditor\ParsableMenu;
use MediaWiki\Extension\MenuEditor\Parser\IMenuParser;
use MediaWiki\Extension\MenuEditor\Parser\WikitextMenuParser;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Title\Title;

class MediawikiSidebar extends GenericMenu implements ParsableMenu, EditPermissionProvider {

	/**
	 * @inheritDoc
	 */
	public function getRLModule(): string {
		return "ext.menuEditor.tree";
	}

	/**
	 * @inheritDoc
	 */
	public function getJSClassname(): string {
		return "ext.menueditor.ui.data.tree.MediawikiSidebarTree";
	}

	/**
	 * @inheritDoc
	 */
	public function appliesToTitle( Title $title ): bool {
		return $title->getNamespace() === NS_MEDIAWIKI && $title->getDBkey() === 'Sidebar';
	}

	/**
	 * @inheritDoc
	 */
	public function getKey(): string {
		return 'mediawiki-sidebar';
	}

	/**
	 * @param Title $title
	 * @param RevisionRecord|null $revision
	 *
	 * @return IMenuParser
	 * @throws \MWException
	 */
	public function getParser( Title $title, ?RevisionRecord $revision = null ): IMenuParser {
		if ( !$revision ) {
			$revision = $this->parserFactory->getRevisionForText( '', $title );
		}
		return new WikitextMenuParser(
			$revision, $this->getProcessors()
		);
	}

	/**
	 * @return string[]
	 */
	public function getAllowedNodes(): array {
		return [ 'menu-raw-text', 'menu-two-fold-link-spec', 'mediawiki-sidebar-keyword' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getToolbarItems(): array {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function getEditRight(): string {
		return 'editinterface';
	}
}
