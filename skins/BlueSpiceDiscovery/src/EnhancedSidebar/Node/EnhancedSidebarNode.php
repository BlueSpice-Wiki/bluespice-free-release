<?php

namespace BlueSpice\Discovery\EnhancedSidebar\Node;

use InvalidArgumentException;
use MediaWiki\Extension\MenuEditor\Node\MenuNode;
use MediaWiki\Message\Message;
use MediaWiki\User\User;

abstract class EnhancedSidebarNode extends MenuNode {

	/** @var mixed */
	protected $hidden;
	/** @var array */
	protected $children = [];
	/** @var string */
	protected $text;
	/**
	 * @var array|mixed
	 */
	private $classes;
	/**
	 * @var mixed|string
	 */
	private $iconCls;

	/**
	 * @param array $data
	 */
	public function __construct( array $data ) {
		parent::__construct( $data['level'] ?? -1, '' );
		$this->hidden = $data['hidden'] ?? '';
		if ( !isset( $data['text'] ) ) {
			throw new InvalidArgumentException(
				$this->getType() . ' requires "text" parameter'
			);
		}
		$this->text = $data['text'];
		$this->classes = $data['classes'] ?? [];
		$this->iconCls = $data['icon-cls'] ?? '';
	}

	/**
	 * @param User $user
	 * @return bool
	 */
	public function isHidden( User $user ): bool {
		// No user context check by default
		return (bool)$this->hidden;
	}

	/**
	 * Serialize in format to be consumed by a tree
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function treeSerialize(): array {
		$data = [
			'id' => $this->generateId(),
			'text' => $this->getDisplayText(),
		];

		$classes = $this->getOutputCssClasses();
		if ( !empty( $classes ) ) {
			$data['classes'] = $classes;
		}
		if ( $this->iconCls ) {
			$data['icon-cls'] = $this->iconCls;
		}

		return $data;
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
	public function addChild( EnhancedSidebarNode $child ) {
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
	protected function generateId(): string {
		return substr(
			md5( $this->getType() . $this->text . $this->getLevel() ),
			-5
		);
	}

	/**
	 * @return array
	 */
	protected function getOutputCssClasses(): array {
		return $this->classes;
	}

	/**
	 * @return string
	 */
	protected function getDisplayText(): string {
		$msg = Message::newFromKey( $this->text );
		if ( $msg->exists() ) {
			return $msg->plain();
		}

		return $this->text;
	}
}
