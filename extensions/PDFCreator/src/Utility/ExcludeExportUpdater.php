<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMElement;
use DOMXPath;

class ExcludeExportUpdater {

	/**
	 * @param ExportPage[] $pages
	 */
	public function execute( $pages ) {
		foreach ( $pages as $page ) {
			if ( $page instanceof ExportPage === false ) {
				continue;
			}

			$xpath = new DOMXPath( $page->getDOMDocument() );
			$excludeStartElements = $xpath->query(
				'//div[contains(@class, "pdfcreator-excludestart")]',
				$page->getDOMDocument()
			);
			if ( !$excludeStartElements ) {
				continue;
			}
			/** @var DOMElement */
			foreach ( $excludeStartElements as $startSpan ) {
				$parent = $startSpan->parentNode;
				/** @var DOMElement */
				$sibling = $startSpan->nextSibling;
				if ( $parent ) {
					$parent->removeChild( $startSpan );
				}

				while ( $sibling ) {
					$nextSibling = $sibling->nextSibling;

					if ( $sibling->nodeType === XML_ELEMENT_NODE ) {
						if ( $sibling->tagName === 'div' &&
							strpos( $sibling->getAttribute( 'class' ), 'pdfcreator-excludeend' ) !== false ) {
							$parent->removeChild( $sibling );
							break;
						}
					}

					$hasEndChild = $xpath->query( ".//*[contains(@class, 'pdfcreator-excludeend')]", $sibling );
					if ( $hasEndChild->length > 0 ) {
						$parent->removeChild( $sibling );
						break;
					}

					$parent->removeChild( $sibling );
					$sibling = $nextSibling;
				}
			}
		}
	}
}
