<?php

namespace BlueSpice\TagCloud\Integration\PDFCreator\Processor;

use DOMXPath;
use MediaWiki\Extension\PDFCreator\IProcessor;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Extension\PDFCreator\Utility\ExportPage;

class RemoveTagCloudStyle implements IProcessor {

	/**
	 * @inheritDoc
	 */
	public function execute(
		array &$pages, array &$images, array &$attachments, ExportContext $context, string $module = '', $params = []
	): void {
		/** @var ExportPage $page */
		foreach ( $pages as &$page ) {
			$dom = $page->getDOMDocument();
			$xpath = new DOMXPath( $dom );
			$items = $xpath->query(
				"//ul[contains(@class, 'bs-tagcloud') and contains(@class, 'text')]/li"
			);

			foreach ( $items as $item ) {
				if ( $item->hasAttribute( 'style' ) ) {
					$item->removeAttribute( 'style' );
				}
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getPosition(): int {
		return 50;
	}
}
