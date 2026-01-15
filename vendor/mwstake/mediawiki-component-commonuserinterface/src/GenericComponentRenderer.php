<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

class GenericComponentRenderer {

	/**
	 *
	 * @var ComponentManager
	 */
	protected $componentManager = [];

	/**
	 *
	 * @var RendererDataTreeBuilder
	 */
	protected $rendererDataTreeBuilder = null;

	/**
	 *
	 * @var RendererDataTreeRenderer
	 */
	protected $RendererDataTreeRenderer = null;

	/**
	 *
	 * @param ComponentManager $componentManager
	 * @param RendererDataTreeBuilder $rendererDataTreeBuilder
	 * @param RendererDataTreeRenderer $RendererDataTreeRenderer
	 */
	public function __construct( $componentManager, $rendererDataTreeBuilder,
	$RendererDataTreeRenderer ) {
		$this->componentManager = $componentManager;
		$this->rendererDataTreeBuilder = $rendererDataTreeBuilder;
		$this->RendererDataTreeRenderer = $RendererDataTreeRenderer;
	}

	/**
	 *
	 * @param IComponent $component
	 * @return string
	 */
	public function getHtml( $component ): string {
		$componentTree = $this->componentManager->getCustomComponentTree( $component );
		$rendererDataTree = $this->rendererDataTreeBuilder->getRendererDataTree( $componentTree );
		return $this->RendererDataTreeRenderer->getHtml( $rendererDataTree );
	}

}
