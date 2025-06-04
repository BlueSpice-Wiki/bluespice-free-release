<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMElement;
use DOMXPath;
use MediaWiki\Title\TitleFactory;

class LinkUpdater {

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
	public function execute( $pages ): array {
		foreach ( $pages as $page ) {
			$this->updateInternalLink( $page );
		}
		return $pages;
	}

	/**
	 * @param ExportPage $page
	 * @return void
	 */
	private function updateInternalLink( ExportPage $page ) {
		$dom = $page->getDOMDocument();
		$xpath = new DOMXPath( $dom );

		$elements = $xpath->query( "//a[@title and not(contains(@class, 'media'))]" );
		foreach ( $elements as $element ) {
			if ( $element instanceof DOMElement === false ) {
				continue;
			}

			if ( !$element->hasAttribute( 'title' ) ) {
				continue;
			}
			$titleAttr = $element->getAttribute( 'title' );
			$title = $this->titleFactory->newFromText( $titleAttr );
			if ( !$title ) {
				continue;
			}
			$element->setAttribute( 'href', $title->getFullURL() );
		}
	}
}
