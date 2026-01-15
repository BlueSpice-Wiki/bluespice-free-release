<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

class ExportResources {

	/** @var string */
	private $html = '';

	/** @var array */
	private $stylePaths = [];

	/** @var array */
	private $imagePaths = [];

	/** @var array */
	private $attachmentPaths = [];

	/**
	 * @param string $html
	 * @param array $stylePaths
	 * @param array $imagePaths
	 * @param array $attachmentPaths
	 */
	public function __construct(
		string $html, array $stylePaths,
		array $imagePaths, array $attachmentPaths
	) {
		$this->html = $html;
		$this->stylePaths = $stylePaths;
		$this->imagePaths = $imagePaths;
		$this->attachmentPaths = $attachmentPaths;
	}

	/**
	 * @return string
	 */
	public function getHtml(): string {
		return $this->html;
	}

	/**
	 * @return array
	 */
	public function getStylesheetPaths(): array {
		return $this->stylePaths;
	}

	/**
	 * @return array
	 */
	public function getImagePaths(): array {
		return $this->imagePaths;
	}

	/**
	 * @return array
	 */
	public function getAttachmentPaths(): array {
		return $this->attachmentPaths;
	}
}
