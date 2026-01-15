<?php

namespace BlueSpice\Discovery\SkinSlotRenderer;

use BlueSpice\Discovery\MetaItemsManager;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;

class DataAfterTitleSkinSlotRenderer extends ExtendedSkinSlotRendererBase {

	public const REG_KEY = 'DataAfterTitle';

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
		return 'data-after-title';
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
	 * @param array $data
	 * @return string
	 */
	public function getHtml( $data = [] ): string {
		$skinSlots = $this->skinSlotRegistry->getSkinSlots();
		$items = $skinSlots[static::REG_KEY];

		$services = MediaWikiServices::getInstance();
		/** @var MetaItemsManager */
		$metaItemsManager = $services->get( 'BlueSpiceDiscoveryMetaItemManager' );
		$metaItems = $this->getMetaItems( $metaItemsManager );

		if ( empty( $items ) && empty( $metaItems ) ) {
			return '';
		}

		if ( !empty( $items ) ) {
			foreach ( $items as $id => $item ) {
				if ( !is_callable( $item['factory'] ) ) {
					continue;
				}
				$component = call_user_func_array( $item['factory'], [] );
				if ( !( $component instanceof IComponent ) ) {
					continue;
				}
				$notRendered = true;
				foreach ( $metaItems as $metaItem ) {
					if ( $component->getId() === $metaItem->getId() ) {
						$notRendered = false;
					}
				}
				if ( $notRendered === false ) {
					continue;
				}
				$metaItems[] = $component;
			}
		}

		$this->sortItems( $items );
		$innerHtml = '';

		if ( !empty( $metaItems ) ) {
			foreach ( $metaItems as $id => $item ) {
				$component = $item;
				if ( !( $component instanceof IComponent ) ) {
					continue;
				}

				$componentTree = $this->componentManager->getCustomComponentTree(
					$component,
					$data
				);

				if ( !empty( $componentTree ) ) {
					$rendererDataTree = $this->rendererDataTreeBuilder->getRendererDataTree( [
						array_pop( $componentTree )
					] );
					$innerHtml .= $this->buildOpeningItemWrapperHtml( $component->getId() );
					$innerHtml .= $this->rendererDataTreeRenderer->getHtml( $rendererDataTree );
					$innerHtml .= $this->buildClosingItemWrapperHtml();
				}
			}
		}

		$html = '';
		if ( $innerHtml !== '' ) {
			$html .= $this->buildOpeningConainerWrapperHtml();
			$html .= $innerHtml;
			$html .= $this->buildClosingConainerWrapperHtml();
		}

		return $html;
	}

	/**
	 *
	 * @param MetaItemsManager $metaItemsManager
	 * @return array
	 */
	protected function getMetaItems( $metaItemsManager ): array {
		return $metaItemsManager->getMetaItemsFromConfigVar( 'DiscoveryMetaItemsHeader' );
	}
}
