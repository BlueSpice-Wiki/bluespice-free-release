<?php

namespace BlueSpice\Discovery\SkinSlotRenderer;

class DataAfterContentSkinSlotRenderer extends ExtendedSkinSlotRendererBase {

	public const REG_KEY = 'DataAfterContent';

	/**
	 *
	 * @param array &$items
	 * @return void
	 */
	protected function sortItems( &$items ): void {
		ksort( $items );
		usort( $items, static function ( $itemOne, $itemTwo ) {
			$item1SortKey = isset( $itemOne['position'] ) ? $itemOne['position'] : 100;
			$item2SortKey = isset( $itemTwo['position'] ) ? $itemTwo['position'] : 100;

			return $item1SortKey > $item2SortKey ? 1 : 0;
		} );
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
		return 'data-after-content';
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

	/**
	 *
	 * @return string
	 */
	protected function buildOpeningConainerWrapperHtml(): string {
		$html = $this->buildOpeningHtml(
			$this->getContainerWrapperTag(),
			$this->getContainerWrapperId(),
			$this->getContainerWrapperClasses(),
			$this->getContainerWrapperAriaAttributes(),
			$this->getContainerWrapperDataAttributes(),
			'complementary'
		);

		return $html;
	}
}
