<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Component;

use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\IRestrictedComponent;
use MWStake\MediaWiki\Component\CommonUserInterface\ITextLink;

class RestrictedTextLink extends ComponentBase implements ITextLink, IRestrictedComponent {

	/**
	 *
	 * @param array $options
	 */
	public function __construct( $options ) {
		$this->options = array_merge(
			[
				'role' => '',
				'id' => 'restircted-text-link',
				'classes' => [],
				'href' => '',
				'text' => new RawMessage( 'RestrictedTextLink' ),
				'title' => new RawMessage( 'RestrictedTextLink' ),
				'aria-label' => new RawMessage( 'RestrictedTextLink' ),
				'rel' => '',
				'data' => [],
				'aria' => [],
				'permissions' => [],
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
	public function getRole(): string {
		return $this->options['role'];
	}

	/**
	 * @inheritDoc
	 */
	public function getText(): Message {
		return $this->options['text'];
	}

	/**
	 * @inheritDoc
	 */
	public function getTitle(): Message {
		return $this->options['title'];
	}

	/**
	 * @inheritDoc
	 */
	public function getAriaLabel(): Message {
		return $this->options['aria-label'];
	}

	/**
	 * @inheritDoc
	 */
	public function getDataAttributes(): array {
		return $this->options['data'];
	}

	/**
	 * @inheritDoc
	 */
	public function getAriaAttributes(): array {
		return $this->options['aria'];
	}

	/**
	 * @inheritDoc
	 */
	public function getHref(): string {
		return $this->options['href'];
	}

	/**
	 * @inheritDoc
	 */
	public function getRel(): string {
		return $this->options['rel'];
	}

	/**
	 *
	 * @return array
	 */
	public function getPermissions(): array {
		return $this->options['permissions'];
	}

	/**
	 * @param string|Message $stringOrMessage
	 * @return Message
	 */
	protected function ensureMessageObject( $stringOrMessage ) {
		return $stringOrMessage instanceof Message ? $stringOrMessage : new RawMessage( $stringOrMessage );
	}
}
