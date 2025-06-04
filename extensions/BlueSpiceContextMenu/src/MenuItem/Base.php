<?php

namespace BlueSpice\ContextMenu\MenuItem;

use BlueSpice\ContextMenu\IMenuItem;
use MediaWiki\Context\IContextSource;
use MediaWiki\Title\Title;

abstract class Base implements IMenuItem {

	/**
	 *
	 * @var Title
	 */
	protected $title = null;

	/**
	 *
	 * @param Title $title
	 */
	public function __construct( $title ) {
		$this->title = $title;
	}

	/**
	 *
	 * @param Title $title
	 * @return IMenuItem
	 */
	public static function factory( $title ) {
		return new static( $title );
	}

	/**
	 *
	 * @return string
	 */
	public function getJSHandler() {
		return '';
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldList( $context ) {
		return true;
	}

	/**
	 *
	 * @return array
	 */
	public function getChildren() {
		return [];
	}

	/**
	 *
	 * @return int
	 */
	public function getPosition() {
		return 100;
	}

	/**
	 * @return array
	 */
	public function getFlags(): array {
		return [];
	}

	/**
	 * @return bool
	 */
	public function isPrimary(): bool {
		return false;
	}

	/**
	 * @return string|null
	 */
	public function getOverride(): ?string {
		return null;
	}
}
