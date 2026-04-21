<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMElement;
use DOMXPath;

class ImageUrlUpdater {

	/**
	 * @param ExportPage[] $pages
	 * @param WikiFileResource[] $images
	 * @param string $srcAttr
	 * @return void
	 */
	public function execute( array $pages, array $images, $srcAttr = 'src' ) {
		$map = [];
		foreach ( $images as $image ) {
			if ( $image instanceof WikiFileResource === false ) {
				continue;
			}
			foreach ( $image->getURLs() as $url ) {
				$map[$url] = $image->getFilename();
			}

		}

		foreach ( $pages as $page ) {
			if ( $page instanceof ExportPage === false ) {
				continue;
			}

			$xpath = new DOMXPath( $page->getDOMDocument() );
			$imgElements = $xpath->query(
				'//img',
				$page->getDOMDocument()
			);
			if ( !$imgElements ) {
				continue;
			}

			foreach ( $imgElements as $imgElement ) {
				if ( $imgElement instanceof DOMElement === false ) {
					continue;
				}

				if ( !$imgElement->hasAttribute( $srcAttr ) ) {
					continue;
				}

				$src = $imgElement->getAttribute( $srcAttr );
				if ( isset( $map[$src] ) ) {
					$newSrc = urlencode( $map[$src] );
					$imgElement->setAttribute( $srcAttr, "images/{$newSrc}" );
				}
			}
		}
	}
}
