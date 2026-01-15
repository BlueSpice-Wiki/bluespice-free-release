<?php

namespace BlueSpice\Discovery\Renderer;

use MWStake\MediaWiki\Component\CommonUserInterface\ComponentManager;
use MWStake\MediaWiki\Component\CommonUserInterface\RendererDataTreeBuilder;
use MWStake\MediaWiki\Component\CommonUserInterface\RendererDataTreeRenderer;

class ComponentRenderer {

	/**
	 * @var ComponentManager
	 */
	private $componentManager = null;

	/**
	 * @var RendererDataTreeBuilder
	 */
	private $rendererDataTreeBuilder = null;

	/**
	 * @var RendererDataTreeRenderer
	 */
	private $rendererDataTreeRenderer = null;

	/**
	 *
	 * @param ComponentManager $manager
	 * @param RendererDataTreeBuilder $dataTreeBuilder
	 * @param RendererDataTreeRenderer $dataTreeRenderer
	 */
	public function __construct(
		ComponentManager $componentManager,
		RendererDataTreeBuilder $rendererDataTreeBuilder,
		RendererDataTreeRenderer $rendererDataTreeRenderer ) {
		$this->componentManager = $componentManager;
		$this->rendererDataTreeBuilder = $rendererDataTreeBuilder;
		$this->rendererDataTreeRenderer = $rendererDataTreeRenderer;
	}

	/**
	 *
	 * @param IComponent $component
	 * @param array $componentProcessData
	 * @return string
	 */
	public function getComponentHtml( $component, $componentProcessData = [] ): string {
		$componentTree = $this->componentManager->getCustomComponentTree(
			$component,
			$componentProcessData
		);
		if ( empty( $componentTree ) ) {
			return '';
		}

		$rendererDataTree = $this->rendererDataTreeBuilder->getRendererDataTree( [ array_pop( $componentTree ) ] );

		$html = $this->rendererDataTreeRenderer->getHtml( $rendererDataTree );

		return $html;
	}
}
