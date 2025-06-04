<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

class ExportStatus {

	/** @var bool */
	private $isGood;

	/** @var string */
	private $text;

	/**
	 * @param bool $isGood
	 * @param string $text
	 */
	public function __construct( bool $isGood, string $text = '' ) {
		$this->isGood = $isGood;
		$this->text = $text;
	}

	/**
	 * @return bool
	 */
	public function isGood(): bool {
		return $this->isGood;
	}

	/**
	 * @return string
	 */
	public function getText(): string {
		return $this->text;
	}

}
