<?php

namespace BlueSpice\InstanceStatus;

interface IStatusProvider {
	/**
	 * Label for the item layout
	 *
	 * @return string
	 */
	public function getLabel(): string;

	/**
	 * Value of the item. Can be reasonable HTML
	 *
	 * @return string
	 */
	public function getValue(): string;

	/**
	 * Icon for the item
	 * @return string
	 */
	public function getIcon(): string;

	/**
	 * Get item priority
	 *
	 * @return int
	 */
	public function getPriority(): int;
}
