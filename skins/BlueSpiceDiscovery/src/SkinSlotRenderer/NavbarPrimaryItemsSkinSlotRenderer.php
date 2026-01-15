<?php

namespace BlueSpice\Discovery\SkinSlotRenderer;

class NavbarPrimaryItemsSkinSlotRenderer extends ExtendedSkinSlotRendererBase {

	public const REG_KEY = 'NavbarPrimaryItems';

	/**
	 *
	 * @return string
	 */
	protected function getItemWrapperTag(): string {
		return 'li';
	}

	/**
	 *
	 * @return array
	 */
	protected function getItemWrapperClasses(): array {
		return [ 'nav-item' ];
	}
}
