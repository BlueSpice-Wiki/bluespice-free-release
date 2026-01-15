<?php

namespace MediaWiki\Extension\PDFCreator\Target;

use MediaWiki\Extension\PDFCreator\IExportTarget;
use MediaWiki\Extension\PDFCreator\Utility\ExportStatus;
use MediaWiki\Extension\PDFCreator\Utility\TargetResult;

class Download implements IExportTarget {

	/**
	 * @param string $pdfData
	 * @param array $params
	 * @return ITargetResult
	 */
	public function execute( string $pdfData, $params = [] ): TargetResult {
		if ( isset( $params['filename'] ) ) {
			$filename = $params['filename'];
		} else {
			$filename = md5( $pdfData ) . '.pdf';
		}

		$status = new ExportStatus( true );
		return new TargetResult( $status, 'download', $filename, [ 'data' => $pdfData ] );
	}
}
