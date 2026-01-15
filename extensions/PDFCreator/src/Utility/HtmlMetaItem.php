<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

class HtmlMetaItem {

	/** @var string */
	private $name = '';

	/** @var string */
	private $content = '';

	/** @var string */
	private $httpEquiv = '';

	/**
	 * @param string $name
	 * @param string $content
	 * @param string $httpEquiv
	 */
	public function __construct( string $name, string $content, string $httpEquiv = '' ) {
		$this->name = $name;
		$this->content = $content;
		$this->httpEquiv = $httpEquiv;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getContent(): string {
		return $this->content;
	}

	/**
	 * @return string
	 */
	public function getHttpEquiv(): string {
		return $this->httpEquiv;
	}
}
