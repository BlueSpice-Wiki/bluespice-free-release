<?php

namespace BlueSpice\Discovery\Renderer;

use MWStake\MediaWiki\Component\CommonUserInterface\SkinSlotRendererFactory;

class SkinSlotRenderer {

	/**
	 * @var SkinSlotRendererFactory
	 */
	private $skinSlotRendererFactory = null;

	/**
	 * @param SkinSlotRendererFactory $skinSlotRendererFactory
	 */
	public function __construct( SkinSlotRendererFactory $skinSlotRendererFactory ) {
		$this->skinSlotRendererFactory = $skinSlotRendererFactory;
	}

	/**
	 * @param string $skinSlotRegKey
	 * @param array $componentProcessData
	 * @return string
	 */
	public function getSkinSlotHtml( $skinSlotRegKey, $componentProcessData = [] ): string {
		/** @var ExtendedSkinSlotRendererBase */
		$skinSlotRenderer = $this->skinSlotRendererFactory->create( $skinSlotRegKey );
		$html = $skinSlotRenderer->getHtml( $componentProcessData );

		return $html;
	}
}
