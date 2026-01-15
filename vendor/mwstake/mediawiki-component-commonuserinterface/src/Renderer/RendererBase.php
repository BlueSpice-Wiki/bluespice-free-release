<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Renderer;

use HtmlArmor;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponentRenderer;
use TemplateParser;

abstract class RendererBase implements IComponentRenderer {

	/**
	 *
	 * @var string
	 */
	protected $templateBasePath = '';

	/**
	 *
	 * @var TemplateParser
	 */
	private $templateParser = null;

	/**
	 */
	public function __construct() {
		$this->templateBasePath = __DIR__ . '/../../resources/templates/';
	}

	/**
	 * @inheritDoc
	 */
	public function getHtml( $data ): string {
		$templatePathname = $this->getTemplatePathname();
		$templateDirname = dirname( $templatePathname );
		$templateFilename = basename( $templatePathname );
		// TODO: Maybe add a "getTemplateFileExtension" method to the interface?
		$templateFilename = preg_replace( '#\.mustache$#', '', $templateFilename );
		$this->templateParser = new TemplateParser( $templateDirname );
		$data = $this->preprocessData( $data );
		$html = $this->templateParser->processTemplate( $templateFilename, $data );
		// An empty string causes an
		//  PHP Notice: 'Array to string conversion inincludes/TemplateParser.php(173) : eval()'d'
		// and the output in the browser is 'Array'. To avoid this we replace the empty sting.
		if ( $html === '' ) {
			$html = "\0";
		}
		return $html;
	}

	/**
	 * Handle `HtmlArmor`
	 * @param array $data
	 * @return array
	 */
	protected function preprocessData( $data ) {
		$htmlArmorExcludedFieldNames = $this->getHtmlArmorExcludedFields();
		$processedData = [];
		foreach ( $data as $fieldName => $dataValue ) {
			if ( is_array( $dataValue ) ) {
				$dataValue = $this->preprocessData( $dataValue );
			} elseif ( !in_array( $fieldName, $htmlArmorExcludedFieldNames ) ) {
				$dataValue = HtmlArmor::getHtml( $dataValue );
			}
			$processedData[$fieldName] = $dataValue;
		}

		return $processedData;
	}

	/**
	 * Explicitly list field names that should not be automatically handeled with `HtmlArmor`
	 * @return string[]
	 */
	protected function getHtmlArmorExcludedFields() {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function getRLModules(): array {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function getRLModuleStyles(): array {
		return [];
	}
}
