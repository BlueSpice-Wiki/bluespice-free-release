<?php

namespace MediaWiki\Extension\EnhancedUpload;

use MediaWiki\Config\Config;
use MediaWiki\MediaWikiServices;
use MediaWiki\ResourceLoader\Context as ResourceLoaderContext;

class ClientConfig {

	/**
	 * @param ResourceLoaderContext $context
	 * @param Config $config
	 * @return array
	 */
	public static function makeConfigJson(
		ResourceLoaderContext $context,
		Config $config
	) {
		$allowedMimeTypes = [];
		$mimeAnalyzer = MediaWikiServices::getInstance()->getMimeAnalyzer();
		$allowedFilesExtensions = $config->get( 'FileExtensions' );
		foreach ( $allowedFilesExtensions as $ext ) {
			$allowedMimeTypes = array_merge(
				$allowedMimeTypes,
				$mimeAnalyzer->getMimeTypesFromExtension( $ext )
			);
		}
		return [
			'allowedFileExtensions' => $allowedFilesExtensions,
			'allowedMimeTypes' => $allowedMimeTypes
		];
	}
}
