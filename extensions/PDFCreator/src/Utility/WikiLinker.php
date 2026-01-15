<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMElement;
use DOMXPath;
use MediaWiki\Title\TitleFactory;

class WikiLinker {

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
		// Make links unique
		for ( $index = 0; $index < count( $pages ); $index++ ) {
			$page = $pages[$index];

			$dom = $page->getDOMDocument();
			$xpath = new DOMXPath( $dom );

			$elements = $xpath->query( '//*[contains(@class, "internal") and not(contains(@class, "media"))]' );
			foreach ( $elements as $element ) {
				if ( $element instanceof DOMElement === false ) {
					continue;
				}
				if ( $element->hasAttribute( 'title' ) === false ) {
					continue;
				}
				$title = $element->getAttribute( 'title' );
				$title = $this->titleFactory->newFromText( $title );
				$element->setAttribute( 'href', $title->getFullURL() );
			}
		}

		return $pages;
	}
}
