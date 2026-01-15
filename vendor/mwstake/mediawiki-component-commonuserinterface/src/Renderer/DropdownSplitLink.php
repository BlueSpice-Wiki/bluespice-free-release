<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Renderer;

use Exception;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;
use MWStake\MediaWiki\Component\CommonUserInterface\IDropdownSplitLink;

class DropdownSplitLink extends RendererBase {

	/**
	 *
	 * @param IComponent $component
	 * @return bool
	 */
	public function canRender( IComponent $component ): bool {
		return $component instanceof IDropdownSplitLink;
	}

	/**
	 * Having this public should enable client-side rendering
	 *
	 * @param IDropdownSplitLink $component
	 * @param array $subComponentNodes
	 * @return array
	 */
	public function getRendererDataTreeNode( $component, $subComponentNodes ): array {
		$templateData = [];

		if ( $component instanceof IDropdownSplitLink ) {
			$templateData = [
				'id' => $component->getId(),
				'btn-text' => $component->getButtonText()->text(),
				'btn-title' => $component->getButtonTitle()->text(),
				'btn-href' => $component->getButtonHref(),
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
		return $this->templateBasePath . '/dropdown-split-link.mustache';
	}

	/**
	 * @inheritDoc
	 */
	protected function getHtmlArmorExcludedFields() {
		return [ 'cnt-class', 'btn-group-class', 'btn-class', 'id', 'btn-title', 'btn-data',
			'btn-aria-label', 'btn-disabled', 'btn-href', 'btn-text', 'split-btn-class',
			'split-btn-title', 'split-btn-data', 'split-btn-aria-label', 'split-btn-disabled',
			'menu-class' ];
	}
}
