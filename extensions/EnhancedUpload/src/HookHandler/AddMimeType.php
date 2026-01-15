<?php

namespace MediaWiki\Extension\EnhancedUpload\HookHandler;

use MediaWiki\Config\Config;
use MediaWiki\Hook\MimeMagicImproveFromExtensionHook;

class AddMimeType implements MimeMagicImproveFromExtensionHook {

	/** @var Config */
	private $mainConfig;

	/**
	 * @param Config $mainConfig
	 */
	public function __construct( Config $mainConfig ) {
		$this->mainConfig = $mainConfig;
	}

	/**
	 * @inheritDoc
	 */
	public function onMimeMagicImproveFromExtension( $mimeMagic, $ext, &$mime ) {
		$allow = $this->mainConfig->get( 'EnhancedUploadAllowMismatchedMimeTypes' );
		if ( $allow === false ) {
			return;
		}

		// Allow file extensions with mismatched MIME types to be uploaded.
		// If $mime is not allowed, fallback to 'application/octet-stream' (no overwrite).
		$mimeTypes = $mimeMagic->getMimeTypesFromExtension( $ext );
		if ( !in_array( $mime, $mimeTypes ) ) {
			$mime = 'application/octet-stream';
		}
	}
}
