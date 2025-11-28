<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMElement;
use DOMXPath;
use MediaWiki\Utils\UrlUtils;

/**
 * Unused. Will be deleted as soon as possible.
 */
class LinkUpdater {

	/**
	 * @param UrlUtils $urlUtils
	 */
	public function __construct( private readonly UrlUtils $urlUtils ) {
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

		$elements = $xpath->query( "//a[@href and not(contains(@class, 'media'))]" );
		foreach ( $elements as $element ) {
			if ( $element instanceof DOMElement === false ) {
				continue;
			}

			$href = $this->urlUtils->expand( $element->getAttribute( 'href' ) );
			if ( !$href ) {
				continue;
			}

			$element->setAttribute( 'href', $href );
		}
	}
}
