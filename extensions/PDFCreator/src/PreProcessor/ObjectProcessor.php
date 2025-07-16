<?php

namespace MediaWiki\Extension\PDFCreator\PreProcessor;

use DOMElement;
use DOMXPath;
use MediaWiki\Extension\PDFCreator\IPreProcessor;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Extension\PDFCreator\Utility\ExportPage;

/**
 * Replaces all <object type="image/svg+xml"> elements with <img> tags.
 *
 * This ensures compatibility with PDF renderers that do not handle embedded
 * SVGs in <object> elements.
 *
 * - Finds <object> elements with type="image/svg+xml" and a data attribute.
 * - Replaces them with <img> elements using the same source.
 * - Copies relevant attributes: id, title, style, class.
 */
class ObjectProcessor implements IPreProcessor {

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
		foreach ( $pages as $page ) {
			$dom = $page->getDOMDocument();
			$xpath = new DOMXPath( $dom );
			$objectNodes = $xpath->query( '//object[@type="image/svg+xml"][@data]' );

			foreach ( $objectNodes as $object ) {
				/** @var DOMElement $object */
				$dataUrl = $object->getAttribute( 'data' );

				$img = $dom->createElement( 'img' );
				$img->setAttribute( 'src', $dataUrl );

				$allowedAttrs = [ 'id', 'title', 'style', 'class' ];
				foreach ( $allowedAttrs as $attrName ) {
					if ( $object->hasAttribute( $attrName ) ) {
						$img->setAttribute( $attrName, $object->getAttribute( $attrName ) );
					}
				}

				$object->parentNode->replaceChild( $img, $object );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getPosition(): int {
		return 10;
	}

}
