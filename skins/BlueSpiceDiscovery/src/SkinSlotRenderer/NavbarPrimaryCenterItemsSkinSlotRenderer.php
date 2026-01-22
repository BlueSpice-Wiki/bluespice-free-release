<?php

namespace BlueSpice\Discovery\SkinSlotRenderer;

class NavbarPrimaryCenterItemsSkinSlotRenderer extends ExtendedSkinSlotRendererBase {

	public const REG_KEY = 'NavbarPrimaryCenterItems';

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
