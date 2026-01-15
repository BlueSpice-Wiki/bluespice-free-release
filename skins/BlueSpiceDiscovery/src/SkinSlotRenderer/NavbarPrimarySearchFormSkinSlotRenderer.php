<?php

namespace BlueSpice\Discovery\SkinSlotRenderer;

class NavbarPrimarySearchFormSkinSlotRenderer extends ExtendedSkinSlotRendererBase {

	public const REG_KEY = 'NavbarPrimarySearchForm';

	/**
	 *
	 * @param array $data
	 * @return string
	 */
	public function getHtml( $data = [] ): string {
		$html = '';
		$items = $this->componentManager->getSkinSlotComponentTree( static::REG_KEY, $data );
		krsort( $items, SORT_NATURAL );
		if ( !empty( $items ) ) {
			$id = array_key_first( $items );
			$item = $items[$id];
			$rendererDataTree = $this->rendererDataTreeBuilder->getRendererDataTree( [ $id => $item ] );
			$html .= $this->rendererDataTreeRenderer->getHtml( $rendererDataTree );
		}
		return $html;
	}
}
