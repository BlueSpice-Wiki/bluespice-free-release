<?php

namespace BlueSpice\Discovery;

interface ISkinLayout {

	/**
	 *
	 * @return string
	 */
	public function getName(): string;

	/**
	 * Parse templates recursive
	 *
	 * @return bool
	 */
	public function enableRecursivePartials(): bool;

	/**
	 * @return array
	 */
	public function getSkinStructureElements(): array;

	/**
	 * @return array
	 */
	public function getStructureElementNames(): array;
}
