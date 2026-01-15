<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

class ExportSpecification {

	/** @var string */
	private $module;

	/** @var string */
	private $target;

	/** @var string */
	private $templateProvider;

	/** @var string */
	private $backend;

	/** @var string[] */
	private $pages = [];

	/** @var array */
	private $params = [];

	/** @var array */
	private $options = [];

	/**
	 * @param string $module
	 * @param string $templateProvider
	 * @param string $target
	 * @param string $backend
	 * @param array $pages
	 * @param array $params
	 * @param array $options
	 */
	public function __construct(
		string $module, string $templateProvider, string $target,
		string $backend, array $pages, array $params, array $options
	) {
		$this->module = $module;
		$this->templateProvider = $templateProvider;
		$this->target = $target;
		$this->backend = $backend;
		$this->pages = $pages;
		$this->params = $params;
		$this->options = $options;
	}

	/**
	 * Return name of export module
	 *
	 * @return string
	 */
	public function getModule(): string {
		return $this->module;
	}

	/**
	 * Return name of export target
	 *
	 * @return string
	 */
	public function getTarget(): string {
		return $this->target;
	}

	/**
	 * Return name of template provider
	 *
	 * @return string
	 */
	public function getTemplateProvider(): string {
		return $this->templateProvider;
	}

	/**
	 * Return template name
	 *
	 * @return string|null
	 */
	public function getTemplateName(): ?string {
		if ( isset( $this->params['template'] ) && $this->params['template'] !== '' ) {
			return $this->params['template'];
		}
		return false;
	}

	/**
	 * Return array with page names
	 *
	 * @return array
	 */
	public function getPageSpecs(): array {
		return $this->pages;
	}

	/**
	 * @return array
	 */
	public function getParams(): array {
		return $this->params;
	}

	/**
	 * @return array
	 */
	public function getOptions(): array {
		return $this->options;
	}

	/**
	 * @return string
	 */
	public function getBackend(): string {
		return $this->backend;
	}
}
