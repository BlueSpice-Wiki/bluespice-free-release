<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

abstract class SkinSlotRendererBase implements ISkinSlotRenderer {

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
	protected $rendererDataTreeRenderer = null;

	/**
	 *
	 * @var string
	 */
	protected $slotId = '';

	/**
	 *
	 * @param ComponentManager $componentManager
	 * @param RendererDataTreeBuilder $rendererDataTreeBuilder
	 * @param RendererDataTreeRenderer $rendererDataTreeRenderer
	 * @param string $slotId
	 */
	public function __construct( $componentManager, $rendererDataTreeBuilder,
	$rendererDataTreeRenderer, $slotId ) {
		$this->componentManager = $componentManager;
		$this->rendererDataTreeBuilder = $rendererDataTreeBuilder;
		$this->rendererDataTreeRenderer = $rendererDataTreeRenderer;
		$this->slotId = $slotId;
	}
}
