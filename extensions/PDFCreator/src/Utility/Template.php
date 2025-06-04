<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

class Template {

	/** @var array */
	private $params = [];

	/** @var array */
	private $options = [];

	/** @var string */
	private string $intro = '';

	/** @var string */
	private string $header = '';

	/** @var string */
	private string $body = '';

	/** @var string */
	private string $footer = '';

	/** @var string */
	private string $outro = '';

	/** @var TemplateResources */
	private $resource;

	/**
	 * @param string $body
	 * @param string $header
	 * @param string $footer
	 * @param string $intro
	 * @param string $outro
	 * @param TemplateResources $resource
	 * @param array $params
	 * @param array $options
	 */
	public function __construct(
		string $body, string $header, string $footer, string $intro, string $outro,
		TemplateResources $resource, array $params = [], array $options = []
	) {
		$this->body = $body;
		$this->header = $header;
		$this->footer = $footer;
		$this->intro = $intro;
		$this->outro = $outro;
		$this->resource = $resource;
		$this->params = $params;
		$this->options = $options;
	}

	/**
	 * @return string
	 */
	public function getIntro(): string {
		return $this->intro;
	}

	/**
	 * @return string
	 */
	public function getHeader(): string {
		return $this->header;
	}

	/**
	 * @return string
	 */
	public function getBody(): string {
		return $this->body;
	}

	/**
	 * @return string
	 */
	public function getFooter(): string {
		return $this->footer;
	}

	/**
	 * @return string
	 */
	public function getOutro(): string {
		return $this->outro;
	}

	/**
	 * @return TemplateResources
	 */
	public function getResources(): TemplateResources {
		return $this->resource;
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
}
