<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMDocument;
use DOMElement;
use DOMXPath;

class UniqueHtmlIdMaker {

	/**
	 * @param DOMDocument $dom
	 * @param string $uniqueId
	 * @param array $selfUrls
	 * @return void
	 */
	public function execute( DOMDocument $dom, string $uniqueId = '', array $selfUrls = [] ): void {
		if ( $uniqueId === '' ) {
			return;
		}

		$xpath = new DOMXPath( $dom );

		// Update ancor ids
		$this->updateAnchors( $xpath, $uniqueId, $selfUrls );

		// Update self referencing links of pages inside pdf
		$this->updateHyperlinks( $xpath, $uniqueId, $selfUrls );
	}

	/**
	 * @param DOMXPath $xpath
	 * @param string $uniqueId
	 * @param array $selfUrls
	 * @return void
	 */
	private function updateAnchors( DOMXPath $xpath, string $uniqueId = '', array $selfUrls = [] ): void {
		$elements = $xpath->query( '//*[@id]' );

		$nonLiveList = [];
		for ( $index = 0; $index < count( $elements ); $index++ ) {
			$nonLiveList[$index] = $elements[$index];
		}

		for ( $index = 0; $index < count( $nonLiveList ); $index++ ) {
			$element = $elements[$index];

			if ( $element instanceof DOMElement === false ) {
				continue;
			}

			if ( $element->hasAttribute( 'orig-id' ) ) {
				// already mapped id
				continue;
			}

			$id = $element->getAttribute( 'id' );

			if ( $id === '' ) {
				continue;
			}

			if ( isset( $selfUrls[$id] ) ) {
				// already a valid link to firstheading of apage (e.g. toc page)
				continue;
			}

			if ( $id !== $uniqueId ) {
				$newId = "$uniqueId-$id";

				$element->setAttribute( 'id', $newId );
				$element->setAttribute( 'orig-id', $id );
			}
		}
	}

	/**
	 * @param DOMXPath $xpath
	 * @param string $uniqueId
	 * @param array $selfUrls
	 * @return void
	 */
	private function updateHyperlinks( DOMXPath $xpath, string $uniqueId = '', array $selfUrls = [] ): void {
		$map = $this->getUrlMap( $selfUrls );

		$elements = $xpath->query( '//*[@href]' );
		foreach ( $elements as $element ) {
			if ( $element instanceof DOMElement === false ) {
				continue;
			}

			if ( $element->hasAttribute( 'orig-href' ) ) {
				// already mapped href
				continue;
			}

			$href = $origHref = $element->getAttribute( 'href' );
			if ( $href === '' ) {
				continue;
			}

			if ( substr( $href, 0, 1 ) === '#' ) {
				// jumpmark
				$hash = substr( $href, 1 );
				if ( isset( $selfUrls[$hash] ) ) {
					continue;
				} else {
					$element->setAttribute( 'href', "#{$uniqueId}-{$hash}" );
					$element->setAttribute( 'orig-href', $origHref );
				}
			} else {
				$url = '';
				$hash = '';

				// link has url
				if ( str_contains( $href, '#' ) ) {
					// specific jumpmark
					$hashPos = strpos( $href, '#' );
					$hash = substr( $href, $hashPos + 1 );
					$url = substr( $href, 0, $hashPos );
				} else {
					$url = $href;
				}

				if ( isset( $map[$url] ) ) {
					$url = $map[$url];
				} else {
					continue;
				}

				if ( $hash !== '' ) {
					$url .= "-{$hash}";
				}

				$element->setAttribute( 'href', "#{$url}" );
				$element->setAttribute( 'orig-href', $origHref );
			}
		}
	}

	/**
	 * @param array $selfUrls
	 * @return array
	 */
	private function getUrlMap( array $selfUrls ): array {
		$map = [];
		foreach ( $selfUrls as $id => $urls ) {
			foreach ( $urls as $url ) {
				$map[$url] = $id;
			}
		}
		return $map;
	}
}
