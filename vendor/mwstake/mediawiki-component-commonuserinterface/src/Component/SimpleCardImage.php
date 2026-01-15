<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Component;

use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\CardImageType;
use MWStake\MediaWiki\Component\CommonUserInterface\ICardImage;

class SimpleCardImage extends ComponentBase implements ICardImage {

	/**
	 *
	 * @param string $options
	 */
	public function __construct( $options ) {
		$this->options = array_merge(
			[
				'type' => CardImageType::TOP,
				'id' => '',
				'classes' => [],
				'src' => '',
				'alt' => ''
			],
			$options
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return $this->options['id'];
	}

	/**
	 * @inheritDoc
	 */
	public function getClasses(): array {
		return $this->options['classes'];
	}

	/**
	 * @inheritDoc
	 */
	public function getImageType(): string {
		return $this->options['type'];
	}

	/**
	 * @inheritDoc
	 */
	public function getImageSrc(): string {
		return $this->options['src'];
	}

	/**
	 * @inheritDoc
	 */
	public function getImageAltText(): Message {
		return $this->options['alt'];
	}
}
