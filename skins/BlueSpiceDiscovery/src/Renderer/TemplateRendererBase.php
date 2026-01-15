<?php

namespace BlueSpice\Discovery\Renderer;

use BlueSpice\Discovery\ITemplateRenderer;
use MediaWiki\Html\TemplateParser;

abstract class TemplateRendererBase implements ITemplateRenderer {

	/** @var array|null */
	protected $options;
	/** @var string */
	private $templateName = '';
	/** @var string */
	private $templatePath = '';

	/**
	 *
	 * @param array|null $options
	 */
	public function __construct( $options = [] ) {
		$this->options = $options;
	}

	/**
	 * @return string
	 */
	public function getHtml(): string {
		$this->getTemplate();
		$templateParser = new TemplateParser(
			$this->templatePath
		);
		$templateParser->enableRecursivePartials( false );
		$html = $templateParser->processTemplate(
			$this->templateName,
			$this->getParams()
		);
		return $html;
	}

	/**
	 * @inheritDoc
	 */
	private function getTemplate() {
		$templatePath = $this->getTemplatePath();
		$templateParts = explode( '/', $templatePath );
		$this->templateName = array_pop( $templateParts );
		$this->templatePath = implode( '/', $templateParts );
	}

	/**
	 *
	 * @return array
	 */
	public function getParams(): array {
		return $this->options;
	}
}
