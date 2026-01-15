<?php

namespace BlueSpice\Discovery\SkinSlotRenderer;

class DataBeforeContentSkinSlotRenderer extends ExtendedSkinSlotRendererBase {

	public const REG_KEY = 'DataBeforeContent';

	/**
	 *
	 * @param array &$items
	 * @return void
	 */
	protected function sortItems( &$items ): void {
	}

	/**
	 *
	 * @return string
	 */
	protected function getContainerWrapperTag(): string {
		return 'div';
	}

	/**
	 *
	 * @return string
	 */
	protected function getContainerWrapperId(): string {
		return 'data-before-content';
	}

	/**
	 *
	 * @return string
	 */
	protected function getItemWrapperTag(): string {
		return 'div';
	}

	/**
	 *
	 * @param string $id
	 * @return string
	 */
	protected function getItemWrapperId( $id ): string {
		return $id . '-cnt';
	}
}
