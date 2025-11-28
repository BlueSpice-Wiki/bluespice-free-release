<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMElement;
use DOMXPath;
use MediaWiki\Utils\UrlUtils;

class WikiLinker {

	/**
	 * @param UrlUtils $urlUtils
	 */
	public function __construct( private readonly UrlUtils $urlUtils ) {
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

			$elements = $xpath->query( '//a[not(contains(@class, "media"))]' );
			foreach ( $elements as $element ) {
				if ( $element instanceof DOMElement === false ) {
					continue;
				}

				if ( $element->hasAttribute( 'href' ) === false ) {
					continue;
				}

				$href = $element->getAttribute( 'href' );
				if ( substr( $href, 0, 1 ) === '#' ) {
					continue;
				}

				$href = $this->urlUtils->expand( $href );
				if ( !$href ) {
					continue;
				}

				$element->setAttribute( 'href', $href );
			}
		}

		return $pages;
	}
}
