<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Component;

use MediaWiki\Context\IContextSource;

class NullComponent extends ComponentBase {

	/**
	 *
	 * @var string
	 */
	private $id = '';

	/**
	 *
	 * @param string $id
	 */
	public function __construct( $id = 'null-component' ) {
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getId(): string {
		return $this->id;
	}

	/**
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool {
		return false;
	}
}
