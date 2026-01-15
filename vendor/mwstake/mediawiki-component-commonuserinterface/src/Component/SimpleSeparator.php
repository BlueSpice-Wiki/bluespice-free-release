<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Component;

use MWStake\MediaWiki\Component\CommonUserInterface\ISeparator;

class SimpleSeparator extends ComponentBase implements ISeparator {

	/**
	 *
	 * @var array
	 */
	private $classes = [];

	/**
	 *
	 * @param string $class
	 */
	public function __construct( $class ) {
		$this->class = $class;
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return 'separator';
	}

	/**
	 * Raw HTML string
	 *
	 * @return string
	 */
	public function getHtml(): string {
		return $this->html;
	}

	/**
	 *
	 * @return string[]
	 */
	public function getClasses(): array {
		return $this->classes;
	}
}
