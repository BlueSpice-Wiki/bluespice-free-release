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

		$this->updateAnchors( $xpath, $uniqueId );
		if ( !empty( $selfUrls ) ) {
			$this->updateHyperlinks( $xpath, $uniqueId, $selfUrls );
		}
	}

	/**
	 * @param DOMXPath $xpath
	 * @param string $uniqueId
	 * @return void
	 */
	private function updateAnchors( DOMXPath $xpath, string $uniqueId = '' ): void {
		$elements = $xpath->query( '//*[@id]' );

		foreach ( $elements as $element ) {
			if ( $element instanceof DOMElement === false ) {
				continue;
			}

			if ( $element->hasAttribute( 'orig-id' ) ) {
				continue;
			}

			$id = $element->getAttribute( 'id' );
			$newId = "$uniqueId-$id";

			$element->setAttribute( 'id', $newId );
			$element->setAttribute( 'orig-id', $id );
		}
	}

	/**
	 * @param DOMXPath $xpath
	 * @param string $uniqueId
	 * @param array $selfUrls
	 * @return void
	 */
	private function updateHyperlinks( DOMXPath $xpath, string $uniqueId = '', array $selfUrls = [] ): void {
		$elements = $xpath->query( '//*[@href]' );

		foreach ( $elements as $element ) {
			if ( $element instanceof DOMElement === false ) {
				continue;
			}

			if ( $element->hasAttribute( 'orig-href' ) ) {
				continue;
			}

			$href = $element->getAttribute( 'href' );
			$hashPos = strpos( $href, '#' );
			if ( $hashPos === false ) {
				continue;
			} elseif ( $hashPos === 0 ) {
				$anchor = substr( $href, 1 );
				$element->setAttribute( 'href', "#$uniqueId-$anchor" );
				$element->setAttribute( 'orig-href', "#$anchor" );
			} else {
				$target = substr( $href, 0, $hashPos );
				if ( in_array( $target, $selfUrls ) ) {
					$anchor = substr( $href, $hashPos + 1 );
					$element->setAttribute( 'href', "#$uniqueId-$anchor" );
					$element->setAttribute( 'orig-href', "#$anchor" );
				}
			}
		}
	}
}
