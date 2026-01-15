<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use MediaWiki\Title\TitleFactory;

class PageLinker {

	/** @var array */
	private $idMap = [];

	/** @var TitleFactory */
	private $titleFactory;

	/**
	 * @param TitleFactory $titleFactory
	 */
	public function __construct( TitleFactory $titleFactory ) {
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @param ExportPage[] $pages
	 * @return array
	 */
	public function execute( array $pages ): array {
		// Build map
		foreach ( $pages as $page ) {
			$this->addPageToMap( $page );
		}

		$uniqueIdMaker = new UniqueHtmlIdMaker();

		// Make links unique
		for ( $index = 0; $index < count( $pages ); $index++ ) {
			$page = $pages[$index];

			foreach ( $this->idMap as $uniqueId => $hrefs ) {
				$dom = $page->getDOMDocument();
				$uniqueIdMaker->execute( $dom, $uniqueId, $hrefs );
			}
		}

		return $pages;
	}

	/**
	 * @param ExportPage $page
	 * @return void
	 */
	private function addPageToMap( ExportPage $page ): void {
		if ( !$page->getPrefixedDBKey() ) {
			return;
		}

		$title = $this->titleFactory->newFromDBKey( $page->getPrefixedDBKey() );

		$uniqueId = $page->getUniqueId();
		$this->idMap[$uniqueId] = [
			$title->getLocalURL(),
			$title->getFullURL()
		];
	}

}
