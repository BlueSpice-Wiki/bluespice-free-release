<?php

namespace MediaWiki\Extension\PDFCreator\Processor;

use DOMDocument;
use DOMElement;
use MediaWiki\Extension\PDFCreator\IProcessor;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Extension\PDFCreator\Utility\ExportPage;

class ButtonProcessor implements IProcessor {

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

			$this->processbuttonElements( $dom );

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
	private function processbuttonElements( DOMDocument $dom ) {
		$buttonElementList = $dom->getElementsByTagName( 'button' );

		$buttonElements = [];
		for ( $index = 0; $index < $buttonElementList->count(); $index++ ) {
			$buttonElement = $buttonElementList->item( $index );
			$buttonElements[] = $buttonElement;
		}

		for ( $index = 0; $index < count( $buttonElements ); $index++ ) {
			$buttonElement = $buttonElements[ $index ];

			// and get replacement
			$replacement = $this->getButtonReplacement( $buttonElement );

			$buttonElement->replaceWith( $replacement );
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
	 * @param DOMElement $buttonElement
	 * @return DOMElement
	 */
	private function getButtonReplacement( DOMElement $buttonElement ): DOMElement {
		$replacement = $buttonElement->ownerDocument->createElement( 'span' );

		$attributes = $this->getAttributes( $buttonElement );
		foreach ( $attributes as $name => $value ) {
			$replacement->setAttribute( $name, $value );
		}

		$replacement->nodeValue = $buttonElement->nodeValue;

		return $replacement;
	}
}
