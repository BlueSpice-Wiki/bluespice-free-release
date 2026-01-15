<?php

namespace BlueSpice\Discovery\SkinSlotRenderer;

class MainPanelSkinSlotRenderer extends ExtendedSkinSlotRendererBase {

	public const REG_KEY = 'MainLinksPanel';

	/**
	 * @param array &$items
	 * @return void
	 */
	protected function sortItems( &$items ): void {
		parent::sortItems( $items );

		$defaultSortValue = 100;
		uasort( $items, static function ( $a, $b ) use ( $defaultSortValue ) {
			$aSortValue = $a['position'] ?? $defaultSortValue;
			$bSortValue = $b['position'] ?? $defaultSortValue;

			return $aSortValue - $bSortValue;
		} );
	}

	/**
	 *
	 * @return string
	 */
	protected function getContainerWrapperTag(): string {
		return 'ul';
	}

	/**
	 *
	 * @return array
	 */
	protected function getContainerWrapperClasses(): array {
		return [ 'list-group' ];
	}

	/**
	 *
	 * @return array
	 */
	protected function getContainerWrapperAriaAttributes(): array {
		return [
			'labelledby' => 'main-links-panel-head'
		];
	}

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
		return [ 'list-group-item' ];
	}

	/**
	 *
	 * @param array &$rendererDataTree
	 * @return void
	 */
	private function checkActiveState( &$rendererDataTree ) {
		// TODO: Fix active state

		$class = '';
		if ( isset( $rendererDataTree[0]['data']['class'] ) ) {
			$class = $rendererDataTree[0]['data']['class'];
		}
		$localUrl = $this->template->getSkin()->getTitle()->getLocalURL();
		$fullUrl = $this->template->getSkin()->getTitle()->getFullUrl();
		if ( $rendererDataTree[0]['data']['href'] === $localUrl || $rendererDataTree[0]['data']['href'] === $fullUrl ) {
			$class .= ' active';
		}
		if ( $class !== '' ) {
			$rendererDataTree[0]['data']['class'] = $class;
		}
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
			'group'
		);

		return $html;
	}

	/**
	 *
	 * @param string $id
	 * @return string
	 */
	protected function buildOpeningItemWrapperHtml( $id ): string {
		$html = $this->buildOpeningHtml(
			$this->getItemWrapperTag(),
			$this->getItemWrapperId( $id ),
			$this->getItemWrapperClasses(),
			$this->getItemWrapperAriaAttributes(),
			$this->getItemWrapperDataAttributes(),
			'presentation'
		);

		return $html;
	}

	/**
	 *
	 * @param string $tag
	 * @param string $id
	 * @param array $classes
	 * @param array $aria
	 * @param array $data
	 * @param string $role
	 * @return string
	 */
	protected function buildOpeningHtml( $tag, $id, $classes, $aria, $data, $role = '' ): string {
		if ( $tag === '' ) {
			return '';
		}

		$htmlId = '';
		if ( $id !== '' ) {
			$htmlId = ' id="' . $id . '"';
		}

		$htmlClass = '';
		if ( !empty( $classes ) ) {
			$htmlClass = ' class="' . implode( ' ', $classes ) . '"';
		}

		$ariaString = '';
		if ( !empty( $aria ) ) {
			$ariaString = ' ' . $this->ariaAttributesBuilder->toString( $aria );
		}

		$dataString = '';
		if ( !empty( $data ) ) {
			$dataString = ' ' . $this->dataAttributesBuilder->toString( $data );
		}

		$roleString = '';
		$ariaLabelString = '';
		if ( $role !== '' ) {
			$roleString = ' role="' . $role . '"';
		}

		return '<' . $tag . $htmlId . $htmlClass . $ariaString . $dataString . $roleString . $ariaLabelString . '>';
	}
}
