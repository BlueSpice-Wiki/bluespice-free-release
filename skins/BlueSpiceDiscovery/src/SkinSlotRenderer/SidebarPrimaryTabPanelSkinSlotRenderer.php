<?php

namespace BlueSpice\Discovery\SkinSlotRenderer;

class SidebarPrimaryTabPanelSkinSlotRenderer extends ExtendedSkinSlotRendererBase {

	public const REG_KEY = 'SidebarPrimaryTabPanels';

	/**
	 * This class is only here to enable the skin slot.
	 * Items in this skin slot must be an instance of ITabPanel.
	 *
	 * The rendering is done by the StackedTabPanelContainerBase.
	 */
}
