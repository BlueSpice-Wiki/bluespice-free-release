<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use MediaWiki\Message\Message;

interface IMediaObject {

	/**
	 * @return string[]
	 */
	public function getContainerClasses(): array;

	/**
	 * @return string[]
	 */
	public function getImageClasses(): array;

	/**
	 * @return string[]
	 */
	public function getBodyClasses(): array;

	/**
	 *
	 * @return Message
	 */
	public function getImageAltText(): Message;

	/**
	 *
	 * @return string
	 */
	public function getImageSrc(): string;
}
