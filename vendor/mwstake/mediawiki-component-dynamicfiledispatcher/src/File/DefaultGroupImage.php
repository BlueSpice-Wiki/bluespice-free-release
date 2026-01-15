<?php

namespace MWStake\MediaWiki\Component\DynamicFileDispatcher\File;

use MediaWiki\Rest\Stream;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\IDynamicFile;
use Psr\Http\Message\StreamInterface;

class DefaultGroupImage implements IDynamicFile {

	/**
	 * @return string
	 */
	public function getMimeType(): string {
		return 'image/png';
	}

	/**
	 * @inheritDoc
	 */
	public function getStream(): StreamInterface {
		return new Stream( fopen( dirname( __DIR__, 2 ) . '/resources/defaultGroupImage.png', 'rb' ) );
	}
}
