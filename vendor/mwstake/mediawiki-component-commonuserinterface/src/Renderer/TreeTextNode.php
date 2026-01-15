<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Renderer;

use Exception;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;
use MWStake\MediaWiki\Component\CommonUserInterface\ITreeNode;
use MWStake\MediaWiki\Component\CommonUserInterface\ITreeTextNode;

class TreeTextNode extends RendererBase {

	/**
	 *
	 * @param IComponent $component
	 * @return bool
	 */
	public function canRender( IComponent $component ): bool {
		return $component instanceof ITreeTextNode;
	}

	/**
	 * Having this public should enable client-side rendering
	 *
	 * @param IComponent $component
	 * @param array $subComponentNodes
	 * @return array
	 */
	public function getRendererDataTreeNode( $component, $subComponentNodes ): array {
		$templateData = [];

		/** @var IComponent $component */
		if ( $component instanceof ITreeTextNode ) {
			$id = $component->getId();

			$templateData = [
				'id' => $id,
				'role' => $component->getRole(),
				'text' => $component->getText()->text(),
				'labelId' => "$id-label",
				'preHtml' => $component->getPreHtml(),
			];

			$this->getClasses( $component, $templateData );
			$this->getChildren( $component, $subComponentNodes, $templateData );

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
		return $this->templateBasePath . '/tree-text-node.mustache';
	}

	/**
	 * @param ITreeNode $component
	 * @return array
	 */
	protected function getExpandButtonParams( ITreeNode $component ): array {
		$button = [];

		if ( $component->isExpanded() ) {
			$button['hasChildren'] = [ 'expanded' => 'true' ];
			$button['class'] = ' expanded';
		} else {
			$button['hasChildren'] = [ 'expanded' => 'false' ];
			$button['class'] = ' collapsed';
		}

		return $button;
	}

	/**
	 * @param IComponent $component
	 * @param array $subComponentNodes
	 * @param array &$templateData
	 * return void
	 */
	protected function getChildren( IComponent $component,
		array $subComponentNodes, array &$templateData
		): void {
		$class = $templateData['class'] ?? '';

		if ( !empty( $subComponentNodes ) ) {
			$templateData['hasChildren'] = 'true';
			$templateData['children'] = $subComponentNodes;
			$templateData['expandBtn'] = $this->getExpandButtonParams( $component );

			if ( $component->isExpanded() ) {
				$templateData['expanded'] = 'true';
				$templateData['class'] = $class . ' expanded';
			}
		} else {
			$templateData['class'] = $class . ' leaf';
		}
	}

	/**
	 * @param IComponent $component
	 * @param array &$templateData
	 * @return void
	 */
	protected function getClasses( IComponent $component, array &$templateData ): void {
		$classes = $component->getClasses();

		if ( $component->isExpanded() ) {
			$classes[] = 'expanded';
		}

		if ( !empty( $classes ) ) {
			$templateData = array_merge(
				$templateData,
				[
					'class' => ' ' . implode( ' ', $classes )
				]
			);
		}

		if ( in_array( 'new', $classes ) ) {
			$templateData['isNew'] = 'true';
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function getHtmlArmorExcludedFields() {
		return [ 'text' ];
	}

}
