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
	 * @param DOMElement $table
	 * @param array $rows
	 * @param DOMElement $tableHead
	 * @return array
	 */
	private function findTableHeads( $table, $rows, $tableHead ) {
		$firstRow = $rows[0] ?? null;
		if ( !$firstRow instanceof DOMElement ) {
			return;
		}

		$ths = $firstRow->getElementsByTagName( 'th' );
		// 'td' must be 0 if all columns are th.
		$tds = $firstRow->getElementsByTagName( 'td' );
		if ( $ths->length === 0 || $tds->length > 0 ) {
			return;
		}

		$headerRowCount = $this->getHeaderRowCount( $firstRow );
		foreach ( array_slice( $rows, 0, $headerRowCount ) as $tableRow ) {
			$tableHead->appendChild( $tableRow );
		}
		if ( $tableHead->hasChildNodes() ) {
			$table->appendChild( $tableHead );
		}
	}

	/**
	 * @param DOMElement $row
	 * @return int
	 */
	private function getHeaderRowCount( DOMElement $row ): int {
		$rowCount = 1;
		foreach ( $row->childNodes as $cell ) {
			if ( $cell instanceof DOMElement &&
				( $cell->tagName === 'td' || $cell->tagName === 'th' ) ) {
				$rowCount = max( $rowCount, (int)$cell->getAttribute( 'rowspan' ) );
			}
		}
		return $rowCount;
	}
}
