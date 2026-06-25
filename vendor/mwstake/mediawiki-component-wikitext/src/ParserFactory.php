<?php

namespace MWStake\MediaWiki\Component\Wikitext;

use LogicException;
use MediaWiki\Content\WikitextContent;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\Parsoid\Config\DataAccess;
use MediaWiki\Parser\Parsoid\Config\PageConfig;
use MediaWiki\Parser\Parsoid\Config\SiteConfig;
use MediaWiki\Revision\MutableRevisionRecord;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\RevisionStore;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Revision\SlotRoleHandler;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\Wikitext\Parser\WikitextParser;
use MWStake\MediaWiki\Lib\Nodes\INodeProcessor;

class ParserFactory {
	/** @var SiteConfig */
	private $siteConfig;
	/** @var DataAccess */
	private $dataAccess;
	/** @var INodeProcessor[] */
	private $nodeProcessors;
	/** @var TitleFactory */
	private $titleFactory;
	/** @var Parser */
	private $parser;
	/** @var SlotRoleHandler */
	private $slotRoleHandler;
	/** @var MediaWikiServices */
	private $services;

	/**
	 * @param INodeProcessor[] $nodeProcessors
	 * @param TitleFactory $titleFactory
	 * @param RevisionStore $revisionStore
	 * @param Parser $parser
	 * @param SlotRoleHandler $slotRoleHandler
	 */
	public function __construct(
		$nodeProcessors, TitleFactory $titleFactory, RevisionStore $revisionStore,
		Parser $parser, SlotRoleHandler $slotRoleHandler
	) {
		$this->services = MediaWikiServices::getInstance();
		$this->siteConfig = $this->services->getParsoidSiteConfig();
		$this->dataAccess = $this->services->getParsoidDataAccess();
		$this->parser = $parser;
		$this->slotRoleHandler = $slotRoleHandler;
		$this->nodeProcessors = $nodeProcessors;
		$this->titleFactory = $titleFactory;
	}

	/**
	 * Parse raw wikitext
	 *
	 * @param string $text
	 * @return WikitextParser
	 */
	public function newTextParser( $text ): WikitextParser {
		$title = $this->titleFactory->newMainPage();
		$record = $this->getRevisionForText( $text, $title );
		return $this->newRevisionParser( $record );
	}

	/**
	 * @param RevisionRecord $record
	 * @return WikitextParser
	 * @throws LogicException
	 */
	public function newRevisionParser( RevisionRecord $record ): WikitextParser {
		$cm = $record->getContent( SlotRecord::MAIN )->getModel();
		switch ( $cm ) {
			case CONTENT_MODEL_WIKITEXT:
				return new WikitextParser(
					$record, $this->nodeProcessors, $this->siteConfig, $this->dataAccess,
					$this->getPageConfig( $record )
				);
			default:
				throw new LogicException( "Not supported content model: $cm" );
		}
	}

	/**
	 * @return array
	 */
	public function getNodeProcessors(): array {
		return $this->nodeProcessors;
	}

	/**
	 * @param string $text
	 * @param \Title $title
	 * @return RevisionRecord
	 */
	public function getRevisionForText( $text, $title ): RevisionRecord {
		$content = new WikitextContent( $text );
		$revisionRecord = new MutableRevisionRecord( $title );
		$revisionRecord->setSlot(
			SlotRecord::newUnsaved(
				SlotRecord::MAIN,
				$content
			)
		);

		return $revisionRecord;
	}

	/**
	 * Page config object for parsoid
	 * @param RevisionRecord $record
	 * @return PageConfig
	 */
	private function getPageConfig( RevisionRecord $record ) {
		return $this->services->getParsoidPageConfigFactory()->create(
			$record->getPage(),
			$record->getUser(),
			$record
		);
	}
}
