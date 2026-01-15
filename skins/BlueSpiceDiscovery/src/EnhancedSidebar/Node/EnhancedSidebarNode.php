<?php

namespace BlueSpice\Discovery\EnhancedSidebar\Node;

use InvalidArgumentException;
use MediaWiki\Extension\MenuEditor\Node\MenuNode;
use MediaWiki\Message\Message;

abstract class EnhancedSidebarNode extends MenuNode {

	/** @var array */
	protected $children = [];

	/** @var string */
	protected $text;

	/** @var string[] */
	private $classes;

	/** @var string */
	private $iconCls;

	/** @var string */
	protected $hidden;

	/**
	 * @param array $data
	 */
	public function __construct( array $data ) {
		parent::__construct( $data['level'] ?? -1, '' );

		if ( !isset( $data['text'] ) ) {
			throw new InvalidArgumentException(
				$this->getType() . ' requires "text" parameter'
			);
		}

		$this->hidden = $data['hidden'] ?? '';
		$this->text = $data['text'];
		$this->classes = $data['classes'] ?? [];
		$this->iconCls = $data['icon-cls'] ?? '';
	}

	/**
	 * @return string
	 */
	public function getReadRestriction(): string {
		return $this->hidden;
	}

	/**
	 * @return string|null
	 */
	public function getTarget(): ?string {
		return null;
	}

	/**
	 * @return string
	 */
	public function getIconCls(): string {
		return $this->iconCls;
	}

	public function jsonSerialize(): array {
		return [
			'type' => $this->getType(),
			'text' => $this->text,
			'level' => $this->getLevel(),
			'hidden' => $this->hidden,
			'classes' => $this->classes,
			'icon-cls' => $this->iconCls,
		];
	}

	/**
	 * Serialize in format to be stored on the page
	 *
	 * @return array
	 */
	public function storageSerialize(): array {
		$json = $this->jsonSerialize();
		unset( $json['level'] );

		if ( empty( $this->children ) ) {
			return $json;
		}
		return $json + [
			'children' => array_map( static function ( $child ) {
				return $child->storageSerialize();
			}, $this->children )
		];
	}

	/**
	 * @param EnhancedSidebarNode $child
	 *
	 * @return void
	 */
	public function addChild( EnhancedSidebarNode $child ): void {
		if ( !$this->supportsChildren() ) {
			return;
		}
		$this->children[] = $child;
	}

	/**
	 * @return bool
	 */
	public function supportsChildren(): bool {
		return true;
	}

	/**
	 * All nodes in a tree must have a unique id
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function generateId(): string {
		return substr(
			md5( $this->getType() . $this->text . $this->getLevel() ),
			-5
		);
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

	/**
	 * @return array
	 */
	public function getOutputCssClasses(): array {
		return $this->classes;
	}
}
