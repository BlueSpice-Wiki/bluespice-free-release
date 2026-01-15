<?php

namespace BlueSpice\Discovery;

interface ITabPanelContainer {

	/**
	 *
	 * @return string
	 */
	public function getId(): string;

	/**
	 *
	 * @return string
	 */
	public function getTabPanelRegistryKey(): string;

	/**
	 *
	 * @return array
	 */
	public function getClasses(): array;
}
