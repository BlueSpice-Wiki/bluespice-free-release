<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Renderer;

use Exception;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;
use MWStake\MediaWiki\Component\CommonUserInterface\IDropdownIcon;

class DropdownIcon extends RendererBase {

	/**
	 *
	 * @param IComponent $component
	 * @return bool
	 */
	public function canRender( IComponent $component ): bool {
		return $component instanceof IDropdownIcon;
	}

	/**
	 * Having this public should enable client-side rendering
	 *
	 * @param IDropdownIcon $component
	 * @param array $subComponentNodes
	 * @return array
	 */
	public function getRendererDataTreeNode( $component, $subComponentNodes ): array {
		$templateData = [];

		if ( $component instanceof IDropdownIcon ) {
			$templateData = [
				'id' => $component->getId(),
				'title' => $component->getTitle()->text(),
				'btn-aria-label' => $component->getAriaLabel()->text(),
				'tabindex' => $component->getTabindex()
			];
			if ( !empty( $subComponentNodes ) ) {
				$templateData['body'] = $subComponentNodes;
			}
			if ( !empty( $component->getContainerClasses() ) ) {
				$templateData = array_merge(
					$templateData,
					[
						'cnt-class' => implode( ' ', $component->getContainerClasses() )
					]
				);
			}
			if ( !empty( $component->getButtonClasses() ) ) {
				$templateData = array_merge(
					$templateData,
					[
						'btn-class' => implode( ' ', $component->getButtonClasses() )
					]
				);
			}
			if ( !empty( $component->getIconClasses() ) ) {
				$templateData = array_merge(
					$templateData,
					[
						'icon-class' => implode( ' ', $component->getIconClasses() )
					]
				);
			}
			if ( !empty( $component->getMenuClasses() ) ) {
				$templateData = array_merge(
					$templateData,
					[
						'menu-class' => implode( ' ', $component->getMenuClasses() )
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
		return $this->templateBasePath . '/dropdown-icon.mustache';
	}

	/**
	 * @inheritDoc
	 */
	protected function getHtmlArmorExcludedFields() {
		return [ 'cnt-class', 'tabindex', 'btn-class', 'id', 'title', 'btn-data', 'btn-aria-label',
			'icon-class', 'menu-class' ];
	}
}
