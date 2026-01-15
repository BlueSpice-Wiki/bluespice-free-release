<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use MediaWiki\Message\Message;

interface ICardImage {

	/**
	 * @return string[]
	 */
	public function getClasses(): array;

	/**
	 * @return CardImageType
	 */
	public function getImageType(): string;

	/**
	 *
	 * @return string
	 */
	public function getImageSrc(): string;

	/**
	 *
	 * @return Message
	 */
	public function getImageAltText(): Message;
}
