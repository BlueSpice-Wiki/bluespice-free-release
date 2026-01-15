<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

class TemplateResources {

	/** @var array */
	private $fontPaths = [];

	/** @var array */
	private $stylePaths = [];

	/** @var array */
	private $styleBlocks = [];

	/** @var array */
	private $imagePaths = [];

	/**
	 * @param array $fontPaths
	 * @param array $stylePaths
	 * @param array $styleBlocks
	 * @param array $imagePaths
	 */
	public function __construct(
		array $fontPaths, array $stylePaths, array $styleBlocks = [], array $imagePaths = []
	) {
		$this->fontPaths = $fontPaths;
		$this->stylePaths = $stylePaths;
		$this->styleBlocks = $styleBlocks;
		$this->imagePaths = $imagePaths;
	}

	/**
	 * [ 'filename' => 'absolute filesystem path' ]
	 *
	 * @return array
	 */
	public function getFontPaths(): array {
		return $this->fontPaths;
	}

	/**
	 * [ 'filename' => 'absolute filesystem path' ]
	 *
	 * @return array
	 */
	public function getStylesheetPaths(): array {
		return $this->stylePaths;
	}

	/**
	 * [ 'name' => 'css' ]
	 *
	 * @return array
	 */
	public function getStyleBlocks(): array {
		return $this->styleBlocks;
	}

	/**
	 * [ 'filename' => 'absolute filesystem path' ]
	 *
	 * @return array
	 */
	public function getImagePaths(): array {
		return $this->imagePaths;
	}
}
