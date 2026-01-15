<?php

namespace BlueSpice\Discovery\EnhancedSidebar\Node;

use MediaWiki\Message\Message;

class SubpageListNode extends InternalLinkNode {

	/** @var int */
	private $depth;

	/**
	 * @param array $data
	 */
	public function __construct( array $data ) {
		parent::__construct( $data );
		$this->depth = $data['depth'] ?? 1;
	}

	/**
	 * @return int
	 */
	public function getDepth(): int {
		return $this->depth;
	}

	/**
	 * @return string
	 */
	public function getType(): string {
		return 'enhanced-sidebar-subpage-tree';
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(): array {
		return parent::jsonSerialize() + [
			'depth' => $this->depth
		];
	}

	/**
	 * @return bool
	 */
	public function supportsChildren(): bool {
		// It adds its own children, no children from definition supported
		return false;
	}

	/**
	 * @return array|string[]
	 */
	public function getOutputCssClasses(): array {
		return array_diff( parent::getOutputCssClasses(), [ 'internal' ] );
	}

	/**
	 * @return string
	 */
	public function getDisplayText(): string {
		$msg = Message::newFromKey( $this->text );
		if ( $msg->exists() ) {
			return $msg->plain();
		}

		return $this->text;
	}
}
