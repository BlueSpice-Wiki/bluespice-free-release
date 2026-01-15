<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Renderer;

use Exception;
use MWStake\MediaWiki\Component\CommonUserInterface\AriaAttributesBuilder;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;
use MWStake\MediaWiki\Component\CommonUserInterface\ILinklistGroup;

class LinklistGroup extends RendererBase {

	/**
	 *
	 * @param IComponent $component
	 * @return bool
	 */
	public function canRender( IComponent $component ): bool {
		return $component instanceof ILinklistGroup;
	}

	/**
	 * Having this public should enable client-side rendering
	 *
	 * @param ILinklistGroup $component
	 * @param array $subComponentNodes
	 * @return array
	 */
	public function getRendererDataTreeNode( $component, $subComponentNodes ): array {
		$templateData = [];

		/** @var IComponent $component */
		if ( $component instanceof ILinklistGroup ) {
			$templateData = [
				'id' => $component->getId()
			];
			if ( !empty( $subComponentNodes ) ) {
				$templateData['body'] = $subComponentNodes;
			}
			if ( !empty( $component->getClasses() ) ) {
				$templateData = array_merge(
					$templateData,
					[
						'class' => implode( ' ', $component->getClasses() )
					]
				);
			}
			$aria = $component->getAriaAttributes();
			if ( !empty( $aria ) ) {
				$ariaAttributesBuilder = new AriaAttributesBuilder();
				$templateData = array_merge(
					$templateData,
					[
						'aria' => $ariaAttributesBuilder->toString( $aria )
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
		return $this->templateBasePath . '/linklist-group.mustache';
	}

	/**
	 * `AriaAttributesBuilder` and `DataAttributesBuilder` are already using
	 * `Sanitizer::safeEncodeTagAttributes`
	 * @inheritDoc
	 */
	protected function getHtmlArmorExcludedFields() {
		return [ 'id', 'class', 'aria' ];
	}
}
