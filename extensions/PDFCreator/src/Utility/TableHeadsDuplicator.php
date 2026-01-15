<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMElement;
use DOMXPath;

class TableHeadsDuplicator {

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
			$tableElements = $xpath->query(
				'//table',
				$page->getDOMDocument()
			);
			if ( !$tableElements ) {
				continue;
			}

			foreach ( $tableElements as $tableElement ) {
				if ( $tableElement instanceof DOMElement === false ) {
					continue;
				}

				$classAttribute = $tableElement->getAttribute( 'class' );
				$classes = explode( ' ', $classAttribute );
				if ( !in_array( 'pdf-not-duplicate-header', $classes ) ) {
					$bodys = $this->findTableBodys( $tableElement );

					foreach ( $bodys as $body ) {
						$rows = $this->findTableRows( $body );

						$tableHead = $page->getDOMDocument()->createElement( 'thead' );
						$tableBody = $body;
						$tableElement->removeChild( $body );

						$this->findTableHeads( $tableElement, $rows, $tableHead );

						if ( $tableHead->hasChildNodes() ) {
								$tableElement->appendChild( $tableHead );
						}
						if ( $tableBody->hasChildNodes() ) {
							$tableElement->appendChild( $tableBody );
						}
					}
				}
			}
		}
	}

	/**
	 *
	 * @param DOMElement $table
	 * @return array
	 */
	private function findTableBodys( $table ) {
		$tableElements = [];
		// We only want direct children, so we cannot use getElementsByTagName
		$tableBodys = $table->childNodes;
		foreach ( $tableBodys as $body ) {
			// Filter for <tbody>
			if ( $body instanceof DOMElement && $body->tagName == 'tbody' ) {
				$tableElements[] = $body;
			}
		}
		return $tableElements;
	}

	/**
	 *
	 * @param DOMElement $table
	 * @return array
	 */
	private function findTableRows( $table ) {
		$tableElements = [];
		// We only want direct children, so we cannot use getElementsByTagName
		$tableRows = $table->childNodes;
		foreach ( $tableRows as $row ) {
			// Filter for <tr>
			if ( $row instanceof DOMElement && $row->tagName == 'tr' ) {
				$tableElements[] = $row;
			}
		}
		return $tableElements;
	}

	/**
	 *
	 * @param DOMElement $table
	 * @param array $rows
	 * @param DOMElement $tableHead
	 * @return array
	 */
	private function findTableHeads( $table, $rows, $tableHead ) {
		$firstRow = true;
		foreach ( $rows as $tableRow ) {
			if ( $firstRow ) {
				$ths = $tableRow->getElementsByTagName( 'th' );
				// 'td' must be 0 if all columns are th
				$tds = $tableRow->getElementsByTagName( 'td' );

				if ( ( $ths->length == 0 ) || ( $tds->length > 0 ) ) {
					$firstRow = false;
					continue;
				}
				$tableHead->appendChild( $tableRow );
			} else {
				if ( $tableHead->hasChildNodes() ) {
					$table->appendChild( $tableHead );
				}
			}
		}
	}
}
