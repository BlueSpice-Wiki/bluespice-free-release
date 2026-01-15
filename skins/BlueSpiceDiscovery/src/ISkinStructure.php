<?php

namespace BlueSpice\Discovery;

use MediaWiki\Context\IContextSource;

interface ISkinStructure {

	/**
	 *
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return string
	 */
	public function getTemplatePath(): string;

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool;

	/**
	 * Parse templates recursive
	 *
	 * @return bool
	 */
	public function enableRecursivePartials(): bool;

	/**
	 *
	 * @return array
	 */
	public function getParams(): array;
}
