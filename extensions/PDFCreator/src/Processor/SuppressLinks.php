<?php

namespace MediaWiki\Extension\PDFCreator\Processor;

use DOMDocument;
use DOMElement;
use MediaWiki\Extension\PDFCreator\IProcessor;
use MediaWiki\Extension\PDFCreator\Utility\BoolValueGet;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Extension\PDFCreator\Utility\ExportPage;

/**
 * Suppress all links that lead out of the pdf.
 */
class SuppressLinks implements IProcessor {

	/**
	 * @param ExportPage[] &$pages
	 * @param array &$images
	 * @param array &$attachments
	 * @param ExportContext|null $context
	 * @param string $module
	 * @param array $params
	 * @return void
	 */
	public function execute(
		array &$pages, array &$images, array &$attachments,
		?ExportContext $context = null, string $module = '', $params = []
	): void {
		if ( !isset( $params['suppress-links'] ) || !BoolValueGet::from( $params['suppress-links'] ) ) {
			return;
		}

		for ( $index = 0; $index < count( $pages ); $index++ ) {
			/** @var ExportPage */
			$page = $pages[$index];
			$dom = $page->getDOMDocument();
			$this->removeLinks( $dom );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getPosition(): int {
		return 85;
	}

	/**
	 * @param DOMDocument $dom
	 * @return void
	 */
	private function removeLinks( DOMDocument $dom ): void {
		$anchors = $dom->getElementsByTagName( 'a' );

		$nonLiveList = [];
		foreach ( $anchors as $anchor ) {
			$nonLiveList[] = $anchor;
		}

		foreach ( $nonLiveList as $anchor ) {
			if ( $anchor instanceof DOMElement === false ) {
				continue;
			}
			if ( !$anchor->hasAttribute( 'href' ) ) {
				continue;
			}
			$href = $anchor->getAttribute( 'href' );
			if ( strpos( $href, '#' ) === 0 ) {
				continue;
			}

			$children = [];
			foreach ( $anchor->childNodes as $childNode ) {
				$children[] = $childNode;
			}

			$span = $anchor->ownerDocument->createElement( 'span' );
			foreach ( $children as $child ) {
				$childNode = $anchor->ownerDocument->importNode( $child, true );
				$span->appendChild( $childNode );
			}

			$anchor->parentNode->replaceChild( $span, $anchor );
		}
	}
}
