<?php

namespace MediaWiki\Extension\EnhancedUpload;

use MediaWiki\MediaWikiServices;

class InsertMediaConfig {

	/**
	 * @return array
	 */
	public static function makeConfigJson() {
		$mainConfig = MediaWikiServices::getInstance()->getMainConfig();
		$alignment = $mainConfig->get( 'EnhancedUploadDragDropImagesAlignment' );
		$type = $mainConfig->get( 'EnhancedUploadDragDropImagesType' );
		$height = $mainConfig->get( 'EnhancedUploadDragDropImagesHeight' );
		$width = $mainConfig->get( 'EnhancedUploadDragDropImagesWidth' );

		return [
			'imagesAlignment' => $alignment,
			'imagesType' => $type,
			'imagesHeight' => $height,
			'imagesWidth' => $width
		];
	}
}
