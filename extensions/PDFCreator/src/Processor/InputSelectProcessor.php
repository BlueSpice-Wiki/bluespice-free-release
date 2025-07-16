<?php

namespace MediaWiki\Extension\PDFCreator\Processor;

use DOMDocument;
use DOMElement;
use MediaWiki\Extension\PDFCreator\IProcessor;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Extension\PDFCreator\Utility\ExportPage;

class InputSelectProcessor implements IProcessor {

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
		for ( $index = 0; $index < count( $pages ); $index++ ) {
			/** @var ExportPage */
			$page = $pages[$index];
			$dom = $page->getDomDocument();

			$this->processSelectElements( $dom );

		}
	}

	/**
	 * @inheritDoc
	 */
	public function getPosition(): int {
		return 10;
	}

	/**
	 * @param DOMDocument $dom
	 * @return void
	 */
	private function processSelectElements( DOMDocument $dom ) {
		$selectElementList = $dom->getElementsByTagName( 'select' );

		$selectElements = [];
		for ( $index = 0; $index < $selectElementList->count(); $index++ ) {
			$selectElement = $selectElementList->item( $index );
			$selectElements[] = $selectElement;
		}

		for ( $index = 0; $index < count( $selectElements ); $index++ ) {
			$selectElement = $selectElements[ $index ];

			// Find selected option and get replacement
			$replacement = $this->getSelectedOptionReplacement( $selectElement );

			$selectElement->replaceWith( $replacement );
		}
	}

	/**
	 * @param DOMElement $element
	 * @return array
	 */
	private function getAttributes( DOMElement $element ): array {
		$attributes = [];

		$attributeList = $element->attributes;
		for ( $index = 0; $index < $attributeList->count(); $index++ ) {
			$item = $attributeList->item( $index );

			$name = $item->nodeName;
			$value = $item->nodeValue;

			$attributes[$name] = $value;
		}

		return $attributes;
	}

	/**
	 * @param DOMElement $selectElement
	 * @return DOMElement
	 */
	private function getSelectedOptionReplacement( DOMElement $selectElement ): DOMElement {
		$replacement = $selectElement->ownerDocument->createElement( 'span' );

		$childList = $selectElement->childNodes;
		for ( $index = 0; $index < $childList->count(); $index++ ) {
			$item = $childList->item( $index );

			if ( $item->nodeName !== 'option' ) {
				continue;
			}

			if ( !$item->hasAttributes() ) {
				continue;
			}

			$attributes = $this->getAttributes( $item );

			if ( !isset( $attributes['selected'] ) ) {
				continue;
			}

			foreach ( $attributes as $name => $value ) {
				if ( !in_array( $name, [ 'class', 'style' ] ) ) {
					continue;
				}
				$replacement->setAttribute( $name, $value );
			}

			$replacement->nodeValue = $item->nodeValue;

			break;
		}

		return $replacement;
	}
}
