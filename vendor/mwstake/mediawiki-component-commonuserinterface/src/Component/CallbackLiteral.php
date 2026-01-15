<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Component;

use MWStake\MediaWiki\Component\CommonUserInterface\ILiteral;

class CallbackLiteral extends ComponentBase implements ILiteral {

	/**
	 *
	 * @var string
	 */
	private $id = '';

	/**
	 *
	 * @var callable
	 */
	private $callback = '';

	/**
	 *
	 * @param string $id
	 * @param callable $callback
	 */
	public function __construct( $id, $callback ) {
		$this->id = $id;
		$this->callback = $callback;
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return $this->id;
	}

	/**
	 * Raw HTML string
	 *
	 * @return string
	 */
	public function getHtml(): string {
		return call_user_func_array( $this->callback, [ $this->componentProcessData ] );
	}
}
