<?php

namespace MWStake\MediaWiki\Component\DynamicFileDispatcher;

interface CacheableFile extends IDynamicFile {

	/**
	 * Get the ETag for this file, used for cache validation.
	 * Must be a complete ETag including double quotes, e.g. '"abc123"'.
	 * Return null if ETag is not supported.
	 * @return string|null
	 */
	public function getETag(): ?string;
}
