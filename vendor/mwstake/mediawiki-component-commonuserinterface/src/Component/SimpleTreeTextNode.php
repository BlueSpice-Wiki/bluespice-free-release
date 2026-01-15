<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Component;

use HtmlArmor;
use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\ITreeNode;
use MWStake\MediaWiki\Component\CommonUserInterface\ITreeTextNode;

class SimpleTreeTextNode extends ComponentBase implements ITreeNode, ITreeTextNode {

	/**
	 * @param array $options
	 */
	public function __construct( array $options = [] ) {
		$this->options = array_merge( [
			'id' => 'some-id',
			'items' => [],
			'classes' => [],
			'role' => 'treeitem',
			'text' => '',
			'expanded' => false,
			'preHtml' => new HtmlArmor( '' ),
		], $options );
	}

	/**
	 * @param array $options
	 * @return void
	 */
	public function setNodeOptions( array $options ): void {
		$this->options = array_merge( $this->options, $options );
	}

	/**
	 * @return string
	 */
	public function getId(): string {
		return $this->options['id'];
	}

	/**
	 * @return string[]
	 */
	public function getClasses(): array {
		return $this->options['classes'];
	}

	/**
	 * @return bool
	 */
	public function isExpanded(): bool {
		return $this->options['expanded'];
	}

	/**
	 * One of the `ARIARole::*` constants
	 *
	 * @return string
	 */
	public function getRole(): string {
		return $this->options['role'];
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		return $this->options['items'];
	}

	/**
	 * @return Message
	 */
	public function getText(): Message {
		return new RawMessage( $this->options['text'] );
	}

	/**
	 * @return HtmlArmor
	 */
	public function getPreHtml(): HtmlArmor {
		return $this->options['preHtml'];
	}
}
