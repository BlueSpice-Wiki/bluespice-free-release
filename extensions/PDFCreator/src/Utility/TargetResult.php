<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use MediaWiki\Extension\PDFCreator\ITargetResult;

class TargetResult implements ITargetResult {

	/** @var ExportStatus */
	private $status;

	/** @var string */
	private $target = '';

	/** @var string */
	private $filename = '';

	/** @var array */
	private $data = [];

	/**
	 * @param ExportStatus $status
	 * @param string $target
	 * @param string $filename
	 * @param array $data
	 */
	public function __construct( ExportStatus $status, string $target, string $filename, array $data = [] ) {
		$this->status = $status;
		$this->target = $target;
		$this->filename = $filename;
		$this->data = $data;
	}

	/**
	 * @return ExportStatus
	 */
	public function getStatus(): ExportStatus {
		return $this->status;
	}

	/**
	 * @return string
	 */
	public function getTarget(): string {
		return $this->target;
	}

	/**
	 * @return string
	 */
	public function getFilename(): string {
		return $this->filename;
	}

	/**
	 * @return array
	 */
	public function getData(): array {
		return $this->data;
	}
}
