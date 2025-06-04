<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use MediaWiki\Extension\PDFCreator\ITargetResult;

class ExportResult {

	/** @var ExportStatus */
	private $status;

	/** @var ITargetResult|null */
	private $result;

	/**
	 * @param ExportStatus $status
	 * @param ITargetResult $result
	 */
	public function __construct( ExportStatus $status, ?ITargetResult $result ) {
		$this->result = $result;
		$this->status = $status;
	}

	/**
	 * @return ExportStatus
	 */
	public function getStatus(): ExportStatus {
		return $this->status;
	}

	/**
	 * @return ?ITargetResult
	 */
	public function getResult(): ?ITargetResult {
		return $this->result;
	}

}
