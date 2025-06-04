<?php

use MediaWiki\Extension\EnhancedUpload\AttachmentTagModifier;
use MediaWiki\MediaWikiServices;

// PHP unit does not understand code coverage for this file
// as the @covers annotation cannot cover a specific file
// This is fully tested in ServiceWiringTest.php
// @codeCoverageIgnoreStart

return [
	'EnhancedUploadAttachmentTagModifier' => static function ( MediaWikiServices $services ) {
		return new AttachmentTagModifier( $services->getTitleFactory() );
	},
];

// @codeCoverageIgnoreEnd
