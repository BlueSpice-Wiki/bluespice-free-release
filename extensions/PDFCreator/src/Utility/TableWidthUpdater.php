<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMElement;
use DOMXPath;

class TableWidthUpdater {

	/**
	 * @param ExportPage[] $pages
	 * @return void
	 */
	public function execute( array $pages ) {
		foreach ( $pages as $page ) {
			if ( $page instanceof ExportPage === false ) {
				continue;
			}

			$xpath = new DOMXPath( $page->getDOMDocument() );
			$tableElements = $xpath->query(
				'//table',
				$page->getDOMDocument()
			);
			if ( !$tableElements ) {
				continue;
			}

			foreach ( $tableElements as $tableElement ) {
				if ( $tableElement instanceof DOMElement === false ) {
					continue;
				}

				$classes = $tableElement->getAttribute( 'class' );
				$tableElement->setAttribute( 'class', $classes . ' pdf-correct-table-width' );
			}
		}
	}
}
