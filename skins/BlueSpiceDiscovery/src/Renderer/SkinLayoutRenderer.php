<?php

namespace BlueSpice\Discovery\Renderer;

use BlueSpice\Discovery\IBaseTemplateAware;
use BlueSpice\Discovery\IContextSourceAware;
use BlueSpice\Discovery\ISkinLayout;
use BlueSpice\Discovery\ISkinLayoutAware;
use BlueSpice\Discovery\ISkinLayoutRenderer;
use BlueSpice\Discovery\ITemplateProvider;
use Exception;
use MediaWiki\Html\TemplateParser;
use MediaWiki\Output\OutputPage;

class SkinLayoutRenderer implements ISkinLayoutRenderer {

	/** @var ISkinLayout */
	private $skinLayout = null;

	/**
	 *
	 * @param SkinLayout $skinLayout
	 */
	public function __construct( $skinLayout ) {
		if ( $skinLayout instanceof ITemplateProvider === false ) {
			throw new Exception(
				$skinLayout->getName() . ' is not instanceof ITemplatePathProvider'
			);
		}

		$this->skinLayout = $skinLayout;
	}

	/**
	 *
	 * @param SkinLayout $skinLayout
	 * @return SkinLayoutRenderer
	 */
	public static function factory( $skinLayout ): ISkinLayoutRenderer {
		return new SkinLayoutRenderer( $skinLayout );
	}

	/**
	 *
	 * @return string
	 */
	public function getHtml(): string {
		$html = '';
		if ( $this->skinLayout instanceof ITemplateProvider ) {
			$templateParser = new TemplateParser(
				$this->skinLayout->getTemplatePath()
			);
			$templateParser->enableRecursivePartials(
				$this->skinLayout->enableRecursivePartials()
			);
			$html = $templateParser->processTemplate(
				$this->skinLayout->getTemplateName(),
				$this->getAllStructureElementsHtml()
			);
		}
		return $html;
	}

	/**
	 *
	 * Use this if comonent is called with {{{myStructure}}} in the layout template
	 *
	 * @return array
	 */
	private function getAllStructureElementsHtml(): array {
		$params = [];

		if ( $this->skinLayout instanceof IBaseTemplateAware ) {
			/** @var OutputPage */
			$out = $this->skinLayout->template->getSkin()->getOutput();
			$params['nonce'] = $out->getCSP()->getNonce();
		}

		$skinStructureElements = $this->skinLayout->getSkinStructureElements();
		foreach ( $skinStructureElements as $skinStructureElement ) {
			if ( $skinStructureElement instanceof IBaseTemplateAware ) {
				$skinStructureElement->setBaseTemplate( $this->skinLayout->template );
			}

			if ( $skinStructureElement instanceof IContextSourceAware ) {
				$skinStructureElement->setContextSource( $this->skinLayout->context );
			}

			if ( $skinStructureElement instanceof ISkinLayoutAware ) {
				$skinStructureElement->setSkinLayout( $this->skinLayout );
			}

			$skinStructureRenderer = new SkinStructureRenderer( $skinStructureElement );
			$name = $skinStructureElement->getName();
			$params[$name] = $skinStructureRenderer->getHtml( $this->skinLayout->context );
		}
		return $params;
	}
}
