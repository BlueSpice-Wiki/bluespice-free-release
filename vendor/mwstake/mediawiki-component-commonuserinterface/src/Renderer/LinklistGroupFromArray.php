<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Renderer;

use Exception;
use MWStake\MediaWiki\Component\CommonUserInterface\AriaAttributesBuilder;
use MWStake\MediaWiki\Component\CommonUserInterface\DataAttributesBuilder;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;
use MWStake\MediaWiki\Component\CommonUserInterface\ILinklistGroupFromArray;
use MWStake\MediaWiki\Component\CommonUserInterface\IListRoleProvider;

class LinklistGroupFromArray extends RendererBase {

	/**
	 *
	 * @param IComponent $component
	 * @return bool
	 */
	public function canRender( IComponent $component ): bool {
		return $component instanceof ILinklistGroupFromArray;
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
		if ( $component instanceof ILinklistGroupFromArray ) {
			$templateData = [
				'cnt-id' => $component->getId()
			];

			if ( !empty( $component->getContainerClasses() ) ) {
				$templateData = array_merge(
					$templateData,
					[
						'cnt-class' => implode( ' ', $component->getContainerClasses() )
					]
				);
			}
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
			$aria = $component->getAriaAttributes();
			if ( !empty( $aria ) ) {
				$ariaAttributesBuilder = new AriaAttributesBuilder();
				$templateData = array_merge(
					$templateData,
					[
						'cnt-aria' => $ariaAttributesBuilder->toString( $aria )
					]
				);
			}

			if ( $component instanceof IListRoleProvider ) {
				$role = $component->getContainerRole();
				if ( $role !== '' ) {
					$templateData = array_merge(
						$templateData,
						[
							'cnt-role' => $role
						]
					);
				}
				$itemRole = $component->getItemRole();
				if ( $itemRole !== '' ) {
					$templateData = array_merge(
						$templateData,
						[
							'item-role' => $itemRole
						]
					);
				}
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
		return $this->templateBasePath . '/linklist-group-from-array.mustache';
	}

	/**
	 * `AriaAttributesBuilder` and `DataAttributesBuilder` are already using
	 * `Sanitizer::safeEncodeTagAttributes`
	 * @inheritDoc
	 */
	protected function getHtmlArmorExcludedFields() {
		return [ 'cnt-id', 'cnt-class', 'cnt-aria', 'id', 'class', 'href', 'title', 'aria', 'data',
			'rel', 'target' ];
	}
}
