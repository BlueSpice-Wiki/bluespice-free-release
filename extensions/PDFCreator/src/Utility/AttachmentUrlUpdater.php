<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMElement;
use DOMXPath;

class AttachmentUrlUpdater {

	/**
	 * @param ExportPage[] $pages
	 * @param WikiFileResource[] $attachments
	 * @param string $srcAttr
	 * @return void
	 */
	public function execute( array $pages, array $attachments, $srcAttr = 'href' ) {
		$map = [];
		foreach ( $attachments as $attachment ) {
			if ( $attachment instanceof WikiFileResource === false ) {
				continue;
			}
			foreach ( $attachment->getURLs() as $url ) {
				$map[$url] = $attachment->getFilename();
			}

		}

		foreach ( $pages as $page ) {
			if ( $page instanceof ExportPage === false ) {
				continue;
			}

			$xpath = new DOMXPath( $page->getDOMDocument() );
			$attachmentElements = $xpath->query(
				'//a[contains(@class, "media")]',
				$page->getDOMDocument()
			);
			if ( !$attachmentElements ) {
				continue;
			}

			foreach ( $attachmentElements as $attachmentElement ) {
				if ( $attachmentElement instanceof DOMElement === false ) {
					continue;
				}

				if ( !$attachmentElement->hasAttribute( $srcAttr ) ) {
					continue;
				}

				$src = $attachmentElement->getAttribute( $srcAttr );
				if ( isset( $map[$src] ) ) {
					$newSrc = "attachments/{$map[$src]}";
					$attachmentElement->setAttribute( $srcAttr, $newSrc );
					$attachmentElement->setAttribute( 'data-fs-embed-file', 'true' );
				}
			}
		}
	}
}
