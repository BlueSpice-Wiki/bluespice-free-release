<?php

namespace BlueSpice\Discovery\Renderer;

use BlueSpice\Discovery\ISkinStructureRenderer;
use BlueSpice\Discovery\ITemplateProvider;
use Exception;
use MediaWiki\Context\IContextSource;
use MediaWiki\Html\TemplateParser;

class SkinStructureRenderer implements ISkinStructureRenderer {

	/** @var SkinStructureElement */
	private $skinStructureElement = null;

	/**
	 *
	 * @param SkinStructureElement $skinStructureElement
	 */
	public function __construct( $skinStructureElement ) {
		if ( $skinStructureElement instanceof ITemplateProvider === false ) {
			throw new Exception(
				$skinStructureElement->getName() . ' is not instanceof ITemplatePathProvider'
			);
		}

		$this->skinStructureElement = $skinStructureElement;
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return string
	 */
	public function getHtml( $context ): string {
		if ( !$this->skinStructureElement->shouldRender( $context ) ) {
			return '';
		}
		$params = array_merge(
			$this->skinStructureElement->getParams()
		);
		$templateParser = new TemplateParser(
			$this->skinStructureElement->getTemplatePath()
		);
		$templateParser->enableRecursivePartials(
			$this->skinStructureElement->enableRecursivePartials()
		);
		$html = $templateParser->processTemplate(
			$this->skinStructureElement->getTemplateName(),
			$params
		);
		return $html;
	}
}
