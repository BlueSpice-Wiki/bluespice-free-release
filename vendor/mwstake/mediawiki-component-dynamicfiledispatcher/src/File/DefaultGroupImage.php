<?php

namespace MWStake\MediaWiki\Component\DynamicFileDispatcher\File;

use MediaWiki\Rest\Stream;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\CacheableFile;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\IDynamicFile;
use Psr\Http\Message\StreamInterface;

class DefaultGroupImage implements IDynamicFile, CacheableFile {

	/** @var string */
	private $path;

	public function __construct() {
		$this->path = dirname( __DIR__, 2 ) . '/resources/defaultGroupImage.png';
	}

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
		return new Stream( fopen( $this->path, 'rb' ) );
	}

	/**
	 * @inheritDoc
	 */
	public function getETag(): ?string {
		return '"' . md5_file( $this->path ) . '"';
	}
}
