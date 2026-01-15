<?php

namespace MediaWiki\Extension\UserProfile\DynamicFileDispatcher;

use MediaWiki\Rest\Stream;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\IDynamicFile;
use Psr\Http\Message\StreamInterface;

class DirectPathDynamicFile implements IDynamicFile {

	/** @var string */
	protected string $path;

	/** @var string */
	protected string $mime;

	/**
	 * @param string $path
	 * @param string $mime
	 */
	public function __construct( string $path, string $mime ) {
		$this->path = $path;
		$this->mime = $mime;
	}

	/**
	 *
	 * @return string
	 */
	public function getMimeType(): string {
		return $this->mime;
	}

	/**
	 * @return StreamInterface
	 */
	public function getStream(): StreamInterface {
		return new Stream( fopen( $this->path, 'rb' ) );
	}
}
