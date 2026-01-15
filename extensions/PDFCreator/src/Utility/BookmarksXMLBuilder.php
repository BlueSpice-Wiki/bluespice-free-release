<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMDocument;
use DOMNodeList;
use DOMXPath;

class BookmarksXMLBuilder {

	/**
	 * @param ExportPage[] $pages
	 * @return string
	 */
	public function execute( array $pages ): string {
		$bookmarksDOM = new DOMDocument();
		$bookmarksFragment = $bookmarksDOM->createDocumentFragment();
		$bookmarksFragment->appendXML( '<bookmarks></bookmarks>' );
		$bookmarks = $bookmarksFragment->firstChild;

		foreach ( $pages as $page ) {
			if ( $page->getType() !== 'page' && $page->getType() !== 'raw' ) {
				continue;
			}

			$dom = $page->getDOMDocument();
			$id = $this->getIdfromFirstHeading( $dom );
			if ( !$id ) {
				continue;
			}
			$label = $page->getLabel();
			$bookmark = $bookmarks->ownerDocument->createDocumentFragment();
			$bookmark->appendXML( $this->getBookmarkXML( $label, $id ) );
			$bookmarks->appendChild( $bookmark );
		}
		return $bookmarksDOM->saveXML( $bookmarksFragment );
	}

	/**
	 * @param DOMDocument $dom
	 * @return string
	 */
	protected function getIdfromFirstHeading( DOMDocument $dom ): ?string {
		$xpath = new DOMXPath( $dom );
		$firstHeadings = $xpath->query(
			'//h1[contains(@class, "firstHeading")]', $dom
		);
		if ( $firstHeadings instanceof DOMNodeList === false ) {
			return null;
		}
		$firstHeading = $firstHeadings->item( 0 );
		if ( !$firstHeading || !$firstHeading->hasAttribute( 'id' ) ) {
			return null;
		}
		if ( !$firstHeading->nodeValue ) {
			return null;
		}
		return $firstHeading->getAttribute( 'id' );
	}

	/**
	 * @param string $label
	 * @param string $id
	 * @return string
	 */
	protected function getBookmarkXML( string $label, string $id ): string {
		return '<bookmark name="' . $label . '" href="#' . $id . '" />';
	}
}
