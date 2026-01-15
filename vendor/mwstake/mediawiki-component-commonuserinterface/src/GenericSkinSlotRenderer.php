<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

class GenericSkinSlotRenderer extends SkinSlotRendererBase {

	/**
	 * @param array $data - Not used here. This is preserved for SkinSlotRenderers, which create a
	 * custom component tree via `ComponentManager::getCustomComponentTree`, e.g. Within a skin
	 * that needs to pass `QuickTemplate::$data`
	 * @return string
	 */
	public function getHtml( $data = [] ): string {
		$componentTree = $this->componentManager->getSkinSlotComponentTree( $this->slotId );
		$rendererDataTree = $this->rendererDataTreeBuilder->getRendererDataTree( $componentTree );
		$html = $this->rendererDataTreeRenderer->getHtml( $rendererDataTree );

		return $html;
	}

}
