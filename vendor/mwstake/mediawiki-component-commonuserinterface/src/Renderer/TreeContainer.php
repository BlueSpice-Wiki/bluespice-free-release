<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Renderer;

use Exception;
use MWStake\MediaWiki\Component\CommonUserInterface\AriaAttributesBuilder;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;
use MWStake\MediaWiki\Component\CommonUserInterface\ITreeContainer;

class TreeContainer extends RendererBase {

	/**
	 *
	 * @param IComponent $component
	 * @return bool
	 */
	public function canRender( IComponent $component ): bool {
		return $component instanceof ITreeContainer;
	}

	/**
	 * Having this public should enable client-side rendering
	 *
	 * @param ILink $component
	 * @param array $subComponentNodes
	 * @return array
	 */
	public function getRendererDataTreeNode( $component, $subComponentNodes ): array {
		$templateData = [];

		/** @var IComponent $component */
		if ( $component instanceof ITreeContainer ) {
			$templateData = [
				'id' => $component->getId(),
				'role' => $component->getRole(),
			];

			$classes = $component->getClasses();
			if ( !empty( $classes ) ) {
				$templateData = array_merge(
					$templateData,
					[
						'class' => ' ' . implode( ' ', $classes )
					]
				);
			}

			if ( !empty( $subComponentNodes ) ) {
				$templateData['hasItems'] = 'true';
				$templateData['body'] = $subComponentNodes;
			}

			$aria = $component->getAriaAttributes();
			$ariaAttributesBuilder = new AriaAttributesBuilder();
			$ariaAttributes = $ariaAttributesBuilder->toString( $aria );

			$templateData = array_merge(
				$templateData,
				[
					'aria' => $ariaAttributes
				]
			);

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
		return $this->templateBasePath . '/tree-container.mustache';
	}

	/**
	 * `AriaAttributesBuilder` and `DataAttributesBuilder` are already using
	 * `Sanitizer::safeEncodeTagAttributes`
	 * @inheritDoc
	 */
	protected function getHtmlArmorExcludedFields() {
		return [ 'aria' ];
	}
}
