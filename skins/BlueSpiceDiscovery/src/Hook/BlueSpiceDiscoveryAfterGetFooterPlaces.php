<?php

namespace BlueSpice\Discovery\Hook;

interface BlueSpiceDiscoveryAfterGetFooterPlaces {

	/**
	 * @param array &$footerlinks
	 * @return void
	 */
	public function onBlueSpiceDiscoveryAfterGetFooterPlaces( &$footerlinks ): void;
}
