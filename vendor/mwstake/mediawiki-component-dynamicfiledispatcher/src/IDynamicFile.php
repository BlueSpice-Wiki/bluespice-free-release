<?php

namespace MWStake\MediaWiki\Component\DynamicFileDispatcher;

use Psr\Http\Message\StreamInterface;

interface IDynamicFile {

	/**
	 * Get the file mime type
	 * @return string
	 */
	public function getMimeType(): string;

	/**
	 * Get the file stream
	 * @return StreamInterface
	 */
	public function getStream(): StreamInterface;
}
