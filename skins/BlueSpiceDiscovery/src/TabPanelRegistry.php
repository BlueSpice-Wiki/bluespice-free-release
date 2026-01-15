<?php

namespace BlueSpice\Discovery;

use MWStake\MediaWiki\Component\CommonUserInterface\ITabPanel;

class TabPanelRegistry {

	/**
	 *
	 * @var array
	 */
	private $panels = [];

	/**
	 *
	 * @param array $panels
	 */
	public function __construct( $panels ) {
		$this->panels = $panels;
	}

	/**
	 *
	 * @param string $key
	 * @param ITabPanel[] $panel
	 */
	public function register( $key, $panel ): void {
		$this->panels[$key] = $panel;
	}

	/**
	 * @param string $key
	 * @return array
	 */
	public function getRegistryData( $key ): array {
		if ( array_key_exists( $key, $this->panels ) ) {
			return $this->panels[$key];
		}
		return [];
	}
}
