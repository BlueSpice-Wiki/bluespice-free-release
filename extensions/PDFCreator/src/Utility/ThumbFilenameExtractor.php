<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

class ThumbFilenameExtractor {

	/**
	 * @param string $path
	 * @return string
	 */
	public function extractFilename( string $path ): string {
		// get thumb filename
		$thumbFilenamePos = strrpos( $path, '/' );
		$thumbFilename = substr( $path, $thumbFilenamePos + 1 );

		// get original filename
		$origFilenamePath = substr( $path, 0, $thumbFilenamePos );
		$origFilenamePos = strrpos( $origFilenamePath, '/' );
		$origFilename = substr( $origFilenamePath, $origFilenamePos + 1 );

		// get file extensions
		$thumExtPos = strrpos( $thumbFilename, '.' );
		$thumbExt = substr( $thumbFilename, $thumExtPos + 1 );
		$origExtPos = strrpos( $origFilename, '.' );
		$origExt = substr( $origFilename, $origExtPos + 1 );

		if ( $thumbExt === $origExt ) {
			return $origFilename;
		} else {
			return $thumbFilename;
		}
	}

	/**
	 * @param string $localPath
	 * @return bool
	 */
	public function isThumb( string $localPath ): bool {
		$localPath = trim( $localPath, '/' );
		if ( ( strpos( $localPath, 'thumb' ) === 0 )
			|| ( strpos( $localPath, 'images/thumb' ) === 0 ) ) {
			return true;
		}
		return false;
	}
}
