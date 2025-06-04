<?php

namespace MediaWiki\Extension\UserProfile\ProfileImage;

class ProfileImageInfo {

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
	 * @return string
	 */
	public function getMimeType(): string {
		return $this->mime;
	}

	/**
	 * @return string
	 */
	public function getPath(): string {
		return $this->path;
	}
}
