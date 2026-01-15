<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Renderer;

use Exception;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;
use MWStake\MediaWiki\Component\CommonUserInterface\IDropdownIconSplitButton;

class DropdownIconSplitButton extends RendererBase {

	/**
	 *
	 * @param IComponent $component
	 * @return bool
	 */
	public function canRender( IComponent $component ): bool {
		return $component instanceof IDropdownIconSplitButton;
	}

	/**
	 * Having this public should enable client-side rendering
	 *
	 * @param IDropdownIconSplitButton $component
	 * @param array $subComponentNodes
	 * @return array
	 */
	public function getRendererDataTreeNode( $component, $subComponentNodes ): array {
		$templateData = [];

		if ( $component instanceof IDropdownIconSplitButton ) {
			$templateData = [
				'id' => $component->getId(),
				'href' => $component->getHref(),
				'btn-title' => $component->getButtonTitle()->text(),
				'split-btn-title' => $component->getSplitButtonTitle()->text(),
				'btn-aria-label' => $component->getButtonAriaLabel()->text(),
				'split-btn-aria-label' => $component->getSplitButtonAriaLabel()->text()
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
			if ( !empty( $component->getSplitButtonClasses() ) ) {
				$templateData = array_merge(
					$templateData,
					[
						'split-btn-class' => implode( ' ', $component->getSplitButtonClasses() )
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
			if ( !empty( $component->getButtonGroupClasses() ) ) {
				$templateData = array_merge(
					$templateData,
					[
						'btn-group-class' => implode( ' ', $component->getButtonGroupClasses() )
					]
				);
			}
			if ( $component->getButtonRel() !== '' ) {
				$templateData = array_merge(
					$templateData,
					[
						'btn-rel' => $component->getButtonRel()
					]
				);
			}
			if ( $component->buttonIsDisabled() ) {
				$templateData = array_merge(
					$templateData,
					[
						'btn-disabled' => 'true'
					]
				);
			}
			if ( $component->splitButtonIsDisabled() || empty( $subComponentNodes ) ) {
				$templateData = array_merge(
					$templateData,
					[
						'split-btn-disabled' => 'true'
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
		return $this->templateBasePath . '/dropdown-icon-split-button.mustache';
	}

	/**
	 * @inheritDoc
	 */
	protected function getHtmlArmorExcludedFields() {
		return [ 'cnt-class', 'btn-group-class', 'href', 'btn-class', 'id', 'btn-title',
			'btn-data', 'btn-aria-label', 'btn-disabled', 'icon-class', 'split-btn-class',
			'split-btn-title', 'split-btn-data', 'split-btn-aria-label', 'split-btn-disabled',
			'menu-class' ];
	}
}
