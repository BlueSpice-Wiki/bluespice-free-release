<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMElement;
use DOMXPath;

class ThumbWidthUpdater {

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
			$figElements = $xpath->query(
				'//figure[contains(@typeof, "mw:File/Thumb")]',
				$page->getDOMDocument()
			);
			if ( !$figElements ) {
				continue;
			}

			foreach ( $figElements as $figElement ) {
				if ( $figElement instanceof DOMElement === false ) {
					continue;
				}

				$imgElements = $figElement->getElementsByTagName( 'img' );
				if ( count( $imgElements ) < 1 ) {
					continue;
				}

				$imgElement = $imgElements->item( 0 );
				if ( !$imgElement->hasAttribute( 'width' ) ) {
					continue;
				}

				$width = $imgElement->getAttribute( 'width' );

				if ( $width > 650 ) {
					$imgElement->removeAttribute( 'width' );
					$imgElement->removeAttribute( 'height' );

					$classes = $imgElement->getAttribute( 'class' );
					$imgElement->setAttribute( 'class', $classes . ' pdf-correct-image-width' );

					$classes = $figElement->getAttribute( 'class' );
					$figElement->setAttribute( 'class', $classes . ' pdf-correct-image-width' );

				} else {
					$classes = $figElement->getAttribute( 'class' );
					$figElement->setAttribute( 'class', $classes . ' pdf-thumb-width' );
					$figElement->setAttribute( 'style', "max-width:{$width}px;" );
				}
			}
		}
	}
}
