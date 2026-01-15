<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMDocument;
use DOMElement;
use DOMXPath;

class AttachmentFinder extends ImageFinder {

	/**
	 * @param DOMDocument $dom
	 * @return void
	 */
	protected function find( DOMDocument $dom ): void {
		$xpath = new DOMXPath( $dom );
		$attachments = $xpath->query(
			'//a[contains(@class, "media")]',
			$dom
		);

		/** @var FileResolver */
		$fileResolver = $this->getFileResolver();

		/** @var DOMElement */
		foreach ( $attachments as $attachment ) {
			if ( !$attachment->hasAttribute( 'href' ) ) {
				continue;
			}

			$file = $fileResolver->execute( $attachment, 'href' );
			if ( !$file ) {
				continue;
			}
			$absPath = $file->getLocalRefPath();
			$filename = $file->getName();
			$filename = $this->uncollideFilenames( $filename, $absPath );
			$url = $attachment->getAttribute( 'href' );

			if ( !isset( $this->data[$filename] ) ) {
				$this->data[$filename] = [
					'src' => [ $url ],
					'absPath' => $absPath,
					'filename' => $filename,
				];
			} elseif ( $this->data[$filename]['absPath'] === $absPath ) {
				$urls = &$this->data[$filename]['src'];
				if ( !in_array( $url, $urls ) ) {
					$urls[] = $url;
				}
			}
		}
	}

}
