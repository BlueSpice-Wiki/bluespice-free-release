<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Renderer;

use Exception;
use MWStake\MediaWiki\Component\CommonUserInterface\AriaAttributesBuilder;
use MWStake\MediaWiki\Component\CommonUserInterface\ICollapsibleCard;

class CollapsibleCard extends RendererBase {

	/**
	 * @return string
	 */
	public function getTemplatePathname(): string {
		return $this->templateBasePath . 'collapsible-card.mustache';
	}

	/**
	 * @inheritDoc
	 */
	public function getRendererDataTreeNode( $component, $subComponentNodes ): array {
		$templateData = [];

		/** @var IComponent $component */
		if ( $component instanceof ICollapsibleCard ) {
			$templateData = [
				'id' => $component->getId(),
				'text' => $component->getText()->text(),
				'title' => $component->getTitle()->text()
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
			if ( !empty( $component->getHeaderClasses() ) ) {
				$templateData = array_merge(
					$templateData,
					[
						'header-class' => implode( ' ', $component->getHeaderClasses() )
					]
				);
			}
			if ( !empty( $component->getBodyClasses() ) ) {
				$templateData = array_merge(
					$templateData,
					[
						'body-class' => implode( ' ', $component->getBodyClasses() )
					]
				);
			}
			$aria = [
				'label' => $component->getAriaLabel()->text(),
				'controls' => $component->getId() . '-body'
			];
			if ( $component->getExpandedState() ) {
				$aria['expanded'] = 'true';
			} else {
				$aria['expanded'] = 'false';
			}
			$ariaAttributesBuilder = new AriaAttributesBuilder();
			$templateData = array_merge(
				$templateData,
				[
					'aria' => $ariaAttributesBuilder->toString( $aria )
				]
			);
		} else {
			throw new Exception( "Can not extract data from " . get_class( $component ) );
		}

		return $templateData;
	}

	/**
	 * `AriaAttributesBuilder` is already using
	 * `Sanitizer::safeEncodeTagAttributes`
	 * @inheritDoc
	 */
	protected function getHtmlArmorExcludedFields() {
		return [ 'cnt-class', 'id', 'header-class', 'title', 'aria', 'body-class' ];
	}

}
