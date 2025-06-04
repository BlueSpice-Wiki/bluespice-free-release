<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

class UncollideFilename {

	/**
	 * Helper to uncollide filenames when adding files
	 * with a preprocessor or processor
	 *
	 * $data has to be [ filename => path ]
	 *
	 * Returns a unused filename or a aready used filename if absolute filesystem path
	 * is already set in array
	 *
	 * @param string $filename
	 * @param string $absFSPath
	 * @param array $data
	 * @return string
	 */
	public function execute( string $filename, string $absFSPath, array $data ): string {
		$extPos = strrpos( $filename, '.' );
		$ext = substr( $filename, $extPos + 1 );
		$name = substr( $filename, 0, $extPos );

		$uncollide = 1;
		$newFilename = $filename;
		while ( isset( $data[$newFilename] ) && $data[$newFilename] !== $absFSPath ) {
			$uncollideStr = (string)$uncollide;
			$newFilename = "{$name}_{$uncollideStr}.{$ext}";
			$uncollide++;
		}
		return $newFilename;
	}
}
