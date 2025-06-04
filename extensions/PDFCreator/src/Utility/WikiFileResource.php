<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

class WikiFileResource {

	/** @var array */
	private $urls;

	/** @var string */
	private $absolutePath;

	/** @var string */
	private $filename;

	/**
	 * @param array $urls
	 * @param string $absolutePath
	 * @param string $filename
	 */
	public function __construct( array $urls, string $absolutePath, string $filename ) {
		$this->urls = $urls;
		$this->absolutePath = $absolutePath;
		$this->filename = $filename;
	}

	/**
	 * @return string
	 */
	public function getFilename(): string {
		return $this->filename;
	}

	/**
	 * @return string
	 */
	public function getAbsolutePath(): string {
		return $this->absolutePath;
	}

	/**
	 * @return array
	 */
	public function getURLs(): array {
		return $this->urls;
	}
}
