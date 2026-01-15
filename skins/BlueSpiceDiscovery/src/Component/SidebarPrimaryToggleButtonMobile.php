<?php

namespace BlueSpice\Discovery\Component;

class SidebarPrimaryToggleButtonMobile extends SidebarPrimaryToggleButton {

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return 'sb-tgl-mobile-pri';
	}

	/**
	 * @inheritDoc
	 */
	public function getClasses(): array {
		return [ 'ico-btn', 'btn', 'sb-toggle', 'bi-list', 'mr-4', 'my-auto' ];
	}
}
