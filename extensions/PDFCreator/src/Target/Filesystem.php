<?php

namespace MediaWiki\Extension\PDFCreator\Target;

use MediaWiki\Config\Config;
use MediaWiki\Extension\PDFCreator\IExportTarget;
use MediaWiki\Extension\PDFCreator\ITargetResult;
use MediaWiki\Extension\PDFCreator\Utility\ExportStatus;
use MediaWiki\Extension\PDFCreator\Utility\TargetResult;

class Filesystem implements IExportTarget {

	/** @var string */
	private $uploadDirectory;

	/**
	 * @param Config $config
	 */
	public function __construct( Config $config ) {
		$this->uploadDirectory = $config->get( 'UploadDirectory' );
	}

	/**
	 * @param string $pdfData
	 * @param array $params
	 * @return ITargetResult
	 */
	public function execute( string $pdfData, $params = [] ): ITargetResult {
		$path = '';
		if ( isset( $params['filesystem-path'] ) ) {
			$path = $params['filesystem-path'];
		} else {
			$path = $this->uploadDirectory . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'PDFCreator';
		}

		if ( isset( $params['filename'] ) ) {
			$filename = $params['filename'];
		} else {
			$filename = md5( $pdfData ) . '.pdf';
		}

		$this->ensureFileSystemPath( $path );

		$status = file_put_contents( $path . DIRECTORY_SEPARATOR . $filename, $pdfData );
		if ( $status !== false ) {
			$status = new ExportStatus( true );
			return new TargetResult( $status, 'filesystem', $filename, [
				'data' => $path . DIRECTORY_SEPARATOR . $filename,
			] );
		} else {
			$status = new ExportStatus( false, 'Not able to create file.' );
			return new TargetResult( $status, 'filesystem', $filename );
		}
	}

	/**
	 * @param string $path
	 * @return void
	 */
	private function ensureFileSystemPath( string $path ): void {
		if ( !file_exists( $path ) ) {
			mkdir( $path, 0755, true );
		}
	}
}
