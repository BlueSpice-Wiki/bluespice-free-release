<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMElement;
use DOMXPath;

class ImageWidthUpdater {

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
			$imgElements = $xpath->query(
				'//a[contains(@class, "mw-file-description")]/img',
				$page->getDOMDocument()
			);
			if ( !$imgElements ) {
				continue;
			}

			foreach ( $imgElements as $imgElement ) {
				if ( $imgElement instanceof DOMElement === false ) {
					continue;
				}

				if ( !$imgElement->hasAttribute( 'width' ) ) {
					continue;
				}

				$width = $imgElement->getAttribute( 'width' );
				if ( $width > 650 ) {
					$imgElement->removeAttribute( 'width' );
					$imgElement->removeAttribute( 'height' );

					$classes = $imgElement->getAttribute( 'class' );
					$imgElement->setAttribute( 'class', $classes . ' pdf-correct-image-width' );
				}
			}
		}
	}
}
