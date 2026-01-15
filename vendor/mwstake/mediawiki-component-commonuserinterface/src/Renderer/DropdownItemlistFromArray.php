<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Renderer;

use Exception;
use MWStake\MediaWiki\Component\CommonUserInterface\AriaAttributesBuilder;
use MWStake\MediaWiki\Component\CommonUserInterface\DataAttributesBuilder;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;
use MWStake\MediaWiki\Component\CommonUserInterface\IDropdownItemlistFromArray;

class DropdownItemlistFromArray extends RendererBase {

	/**
	 *
	 * @param IComponent $component
	 * @return bool
	 */
	public function canRender( IComponent $component ): bool {
		return $component instanceof IDropdownItemlistFromArray;
	}

	/**
	 * Having this public should enable client-side rendering
	 *
	 * @param IDropdownItemlist $component
	 * @param array $subComponentNodes
	 * @return array
	 */
	public function getRendererDataTreeNode( $component, $subComponentNodes ): array {
		$templateData = [];

		/** @var IComponent $component */
		if ( $component instanceof IDropdownItemlistFromArray ) {
			$templateData = [
					'list-id' => $component->getId()
			];
			$links = $component->getLinks();
			if ( !empty( $links ) ) {
				$dataAttributesBuilder = new DataAttributesBuilder();
				$ariaAttributesBuilder = new AriaAttributesBuilder();
				foreach ( $links as $key => $link ) {
					if ( isset( $link['data'] ) ) {
						$links[$key]['data'] = $dataAttributesBuilder->toString( $link['data'] );
					}
					if ( isset( $link['aria'] ) ) {
						$links[$key]['aria'] = $ariaAttributesBuilder->toString( $link['aria'] );
					}
				}
				$templateData = array_merge(
					$templateData,
					[
						'links' => $links
					]
				);
			}
			if ( !empty( $component->getClasses() ) ) {
				$templateData = array_merge(
					$templateData,
					[
						'list-class' => implode( ' ', $component->getClasses() )
					]
				);
			}
		} else {
			throw new Exception( "Can not extract data from " . get_class( $component ) );
		}

		return $templateData;
	}

	/**
	 * Having this public should enable client-side rendering
	 *
	 * @return string
	 */
	public function getTemplatePathname(): string {
		return $this->templateBasePath . '/dropdown-itemlist-from-array.mustache';
	}

	/**
	 * `AriaAttributesBuilder` and `DataAttributesBuilder` are already using
	 * `Sanitizer::safeEncodeTagAttributes`
	 * @inheritDoc
	 */
	protected function getHtmlArmorExcludedFields() {
		return [ 'list-id', 'list-class', 'id', 'class', 'href', 'title', 'aria', 'data',
			'rel', 'target' ];
	}
}
