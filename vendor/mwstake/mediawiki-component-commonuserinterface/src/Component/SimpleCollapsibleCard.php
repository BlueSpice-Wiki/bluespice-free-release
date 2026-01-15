<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Component;

use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\ICollapsibleCard;

class SimpleCollapsibleCard extends ComponentBase implements ICollapsibleCard {

	/**
	 *
	 * @param string $options
	 */
	public function __construct( $options ) {
		$this->options = array_merge(
			[
				'id' => 'simple-collapsible-card',
				'text' => new RawMessage( 'SimpleCollapsibleCard' ),
				'title' => new RawMessage( 'SimpleCollapsibleCard' ),
				'aria-label' => new RawMessage( 'SimpleCollapsibleCard' ),
				'classes' => [],
				'header-classes' => [],
				'body-classes' => [],
				'items' => [],
				'expanded' => true
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
	public function getContainerClasses(): array {
		return $this->options['classes'];
	}

	/**
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		return $this->options['items'];
	}

	/**
	 *
	 * @return bool
	 */
	public function getExpandedState(): bool {
		return $this->options['expanded'];
	}

	/**
	 * @return string[]
	 */
	public function getHeaderClasses(): array {
		return $this->options['header-classes'];
	}

	/**
	 * @return string[]
	 */
	public function getBodyClasses(): array {
		return $this->options['body-classes'];
	}

	/**
	 * @return Message
	 */
	public function getText(): Message {
		return $this->options['text'];
	}

	/**
	 * @return Message
	 */
	public function getTitle(): Message {
		return $this->options['title'];
	}

	/**
	 * @return Message
	 */
	public function getAriaLabel(): Message {
		return $this->options['aria-label'];
	}

}
