<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMElement;
use DOMText;
use DOMXPath;

class ExcludeExportUpdater {

	/**
	 * @param ExportPage[] $pages
	 */
	public function execute( $pages ) {
		foreach ( $pages as $page ) {
			if ( $page instanceof ExportPage === false ) {
				continue;
			}

			$dom = $page->getDOMDocument();
			$xpath = new DOMXPath( $dom );
			$excludeStartElements = $xpath->query( '//div[contains(@class, "pdfcreator-excludestart")]', $dom );
			if ( !$excludeStartElements ) {
				continue;
			}

			$pageToc = $this->getPageToc( $xpath );

			foreach ( $excludeStartElements as $startSpan ) {
				$parent = $startSpan->parentNode;
				$sibling = $startSpan->nextSibling;
				if ( $parent ) {
					$this->removeNode( $parent, $startSpan, $xpath, $pageToc );
				}

				while ( $sibling ) {
					$nextSibling = $sibling->nextSibling;

					if ( $sibling->nodeType === XML_ELEMENT_NODE ) {
						if ( $sibling->tagName === 'div' &&
							str_contains( $sibling->getAttribute( 'class' ), 'pdfcreator-excludeend' )
						) {
							$this->removeNode( $parent, $sibling, $xpath, $pageToc );
							break;
						}
					}

					$hasEndChild = $xpath->query( ".//*[contains(@class, 'pdfcreator-excludeend')]", $sibling );
					$this->removeNode( $parent, $sibling, $xpath, $pageToc );

					if ( $hasEndChild->length > 0 ) {
						break;
					}

					$sibling = $nextSibling;
				}
			}
		}
	}

	/**
	 * Remove a node from the DOM.
	 * Update page toc if element to remove is present in the toc, identified by its id attribute.
	 *
	 * @param DOMElement $removeFrom
	 * @param DOMElement|DOMText $elToRemove
	 * @param DOMXPath $xpath
	 * @param DOMElement|null $pageToc
	 *
	 * @return void
	 */
	private function removeNode(
		DOMElement $removeFrom,
		DOMElement|DOMText $elToRemove,
		DOMXPath $xpath,
		?DOMElement $pageToc = null
	): void {
		if ( $pageToc && $elToRemove instanceof DOMElement ) {
			$this->updateToc( $pageToc, $elToRemove, $xpath );
		}

		$removeFrom->removeChild( $elToRemove );
	}

	/**
	 * @param DOMElement $pageToc
	 * @param DOMElement $elToRemove
	 * @param DOMXPath $xpath
	 *
	 * @return void
	 */
	private function updateToc(
		DOMElement $pageToc,
		DOMElement $elToRemove,
		DOMXPath $xpath,
	): void {
		$span = $elToRemove->getElementsByTagName( 'span' )->item( 0 );
		if ( $span && $span->hasAttribute( 'id' ) ) {
			$id = $span->getAttribute( 'id' );
			if ( $id ) {
				$tocItem = $xpath->query( ".//a[@href='#$id']", $pageToc );
				if ( $tocItem->length > 0 ) {
					$removeTocListItem = $tocItem->item( 0 )->parentNode;
					$removeTocListItem->parentNode->removeChild( $removeTocListItem );
				}
			}
		}
	}

	/**
	 * @param DOMXPath $xpath
	 *
	 * @return DOMElement|null
	 */
	private function getPageToc( DOMXPath $xpath ): ?DOMElement {
		$pageTocParentId = "toc";
		$pageTocNodeList = $xpath->query( "//*[@id='$pageTocParentId']" );

		if ( $pageTocNodeList->length > 0 ) {
			return $pageTocNodeList->item( 0 );
		}

		return null;
	}
}
