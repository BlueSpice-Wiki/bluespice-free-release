<?php

namespace BlueSpice\Discovery\EnhancedSidebar;

use BlueSpice\Discovery\EnhancedSidebar\Parser as EnhancedSidebarParser;
use MediaWiki\Content\JsonContent;
use MediaWiki\Extension\MenuEditor\EditPermissionProvider;
use MediaWiki\Extension\MenuEditor\Menu\GenericMenu;
use MediaWiki\Extension\MenuEditor\ParsableMenu;
use MediaWiki\Extension\MenuEditor\Parser\IMenuParser;
use MediaWiki\Revision\MutableRevisionRecord;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use MWException;
use MWStake\MediaWiki\Component\Wikitext\ParserFactory;

class Menu extends GenericMenu implements ParsableMenu, EditPermissionProvider {
	/** @var string */
	private $pagename;

	/**
	 * @param ParserFactory $parserFactory
	 * @param string $pagename
	 */
	public function __construct( ParserFactory $parserFactory, string $pagename ) {
		parent::__construct( $parserFactory );
		$this->pagename = $pagename;
	}

	/**
	 * @inheritDoc
	 */
	public function getRLModule(): string {
		return "skin.discovery.enhancedSidebar.tree";
	}

	/**
	 * @inheritDoc
	 */
	public function getJSClassname(): string {
		return "bs.skin.enhancedSidebar.tree.EnhancedSidebarTree";
	}

	/**
	 * @inheritDoc
	 */
	public function appliesToTitle( Title $title ): bool {
		return $title->getNamespace() === NS_MEDIAWIKI &&
			$title->getPrefixedDBkey() === $this->pagename &&
			$title->getContentModel() === 'json';
	}

	/**
	 * @inheritDoc
	 */
	public function getKey(): string {
		return 'enhanced-sidebar';
	}

	/**
	 * @inheritDoc
	 */
	public function getEmptyContent(): array {
		return [];
	}

	/**
	 * @param Title $title
	 * @param RevisionRecord|null $revisionRecord
	 *
	 * @return IMenuParser
	 * @throws MWException
	 */
	public function getParser( Title $title, ?RevisionRecord $revisionRecord = null ): IMenuParser {
		if ( !$revisionRecord ) {
			$content = new JsonContent( '[]' );
			$revisionRecord = new MutableRevisionRecord( $title );
			$revisionRecord->setSlot(
				SlotRecord::newUnsaved(
					SlotRecord::MAIN,
					$content
				)
			);
		}
		return new EnhancedSidebarParser(
			$revisionRecord,
			$this->parserFactory->getNodeProcessors()
		);
	}

	/**
	 * @return string[]
	 */
	public function getAllowedNodes(): array {
		return [
			'enhanced-sidebar-panel-heading', 'enhanced-sidebar-external-link',
			'enhanced-sidebar-internal-link', 'enhanced-sidebar-subpage-tree'
		];
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
