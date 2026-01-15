<?php

namespace BlueSpice\Discovery;

interface ITabPanelRegistry {

	/**
	 *
	 * @return TabPanelRegistry
	 */
	public static function singleton(): TabPanelRegistry;

	/**
	 *
	 * @param string $key
	 * @param array $panel
	 */
	public function register( $key, $panel ): void;

	/**
	 * @param string $key
	 * @return array
	 */
	public function getRegistryData( $key ): array;
}
