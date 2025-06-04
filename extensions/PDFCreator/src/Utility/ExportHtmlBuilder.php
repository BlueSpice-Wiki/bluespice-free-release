<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMDocument;
use DOMElement;
use DOMNode;
use MediaWiki\Extension\PDFCreator\PDFCreator;
use Psr\Log\LoggerInterface;

class ExportHtmlBuilder {

	/**
	 * @param ExportPage[] $pages
	 * @param array $stylesheets
	 * @param array $styleblocks
	 * @param array $meta
	 * @param string $title
	 * @param string $bookmarksXML
	 * @param string $module
	 * @param LoggerInterface|null $logger
	 * @return string
	 */
	public function execute(
		array $pages, array $stylesheets = [], array $styleblocks = [], array $meta = [],
		string $title = '', $bookmarksXML = '', string $module = '', ?LoggerInterface $logger = null
	): string {
		$dom = new DOMDocument();
		$dom->formatOutput = true;
		$dom->preserveWhiteSpace = false;
		$dom->loadXML( PDFCreator::HTML_STUB );
		$head = $dom->getElementsByTagName( 'head' )->item( 0 );
		$body = $dom->getElementsByTagName( 'body' )->item( 0 );

		// document title and meta data
		$this->addTitle( $title, $head );
		$this->addMetaData( $meta, $head );

		// stylesheets
		$this->linkStylesheets( $stylesheets, $head );

		$this->addContent( $pages, $body );

		// style blocks
		$this->moveStyleBlocksToHead( $body, $head );
		$this->addStyleBlocks( $styleblocks, $head );

		// bookmarks
		$this->addBookmarks( $head, $bookmarksXML );

		return $dom->saveXML( $dom->documentElement );
	}

	/**
	 * @param DOMElement $head
	 * @param string $bookmarksXML
	 * @return void
	 */
	private function addBookmarks( DOMElement $head, string $bookmarksXML ): void {
		if ( $bookmarksXML === '' ) {
			return;
		}

		$boomarksFragment = $head->ownerDocument->createDocumentFragment();
		$boomarksFragment->appendXML( $bookmarksXML );
		$head->appendChild( $boomarksFragment );
	}

	/**
	 * @param DOMElement $body
	 * @param DOMElement $head
	 * @return void
	 */
	private function moveStyleBlocksToHead( DOMElement $body, DOMElement $head ): void {
		$styleElements = $body->getElementsByTagName( 'style' );
		$nonLiveList = [];
		foreach ( $styleElements as $element ) {
			$nonLiveList[] = $element;
		}
		foreach ( $nonLiveList as $element ) {
			$head->appendChild( $element );
		}
	}

	/**
	 * @param string $title
	 * @param DOMElement $head
	 * @return void
	 */
	private function addTitle( string $title, DOMElement $head ): void {
		if ( $title !== '' ) {
			$element = $head->ownerDocument->createElement( 'title', $title );
			$head->append( $element );
		}
	}

	/**
	 * @param array $meta
	 * @param DOMElement $head
	 * @return void
	 */
	private function addMetaData( array $meta, DOMElement $head ): void {
		foreach ( $meta as $item ) {
			if ( $item instanceof HtmlMetaItem === false ) {
				continue;
			}
			$element = $head->ownerDocument->createElement( 'meta' );
			$element->setAttribute( 'name', $item->getName() );
			if ( $item->getContent() !== '' ) {
				$element->setAttribute( 'content', $item->getContent() );
			}
			if ( $item->getHttpEquiv() !== '' ) {
				$element->setAttribute( 'httpEquiv', $item->getHttpEquiv() );
			}
			$head->appendChild( $element );
		}
	}

	/**
	 * $stylesheets: [ name => path ]
	 *
	 * @param array $stylesheets
	 * @param DOMElement $head
	 * @return void
	 */
	private function linkStylesheets( array $stylesheets, DOMElement $head ): void {
		foreach ( $stylesheets as $name => $path ) {
			$element = $head->ownerDocument->createElement( 'link' );
			$element->setAttribute( 'type', 'text/css' );
			$element->setAttribute( 'rel', 'stylesheet' );

			if ( !is_string( $name ) ) {
				// Fallback but $stylesheets should be [ name => path ]
				$offset = strrpos( $path, '/' );
				$name = substr( $path, $offset + 1 );
			}
			$element->setAttribute( 'href', "stylesheets/$name" );

			$head->append( $element );
		}
	}

	/**
	 * $styleblocks: [ name => path ]
	 *
	 * @param array $styleblocks
	 * @param DOMElement $head
	 * @return void
	 */
	private function addStyleBlocks( array $styleblocks, DOMElement $head ): void {
		foreach ( $styleblocks as $name => $path ) {
			$css = "/**{$name} */\n$path";
			$element = $head->ownerDocument->createElement( 'style', $css );
			$element->setAttribute( 'type', 'text/css' );
			$head->append( $element );
		}
	}

	/**
	 * @param array $pages
	 * @param DOMNode $body
	 * @return void
	 */
	private function addContent( array $pages, DOMNode $body ): void {
		foreach ( $pages as $page ) {
			$contentNode = $page->getDomDocument()->getElementsByTagName( 'body' )->item( 0 );
			/* if ( !$contentNode || !$contentNode->firstChild ) {
				$node = $body->ownerDocument->createElement( 'p',
				wfMessage( 'pdfcreator-content-non-existing-page' ) );
				$body->appendChild( $node );
				continue;
			}
 */
			$node = $body->ownerDocument->importNode( $contentNode->firstChild, true );
			$body->appendChild( $node );
		}
	}
}
