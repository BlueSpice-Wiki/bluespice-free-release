<?php

namespace BlueSpice\Discovery\Renderer;

class DefaultSearchFormRenderer extends TemplateRendererBase {

	/**
	 *
	 * @return string
	 */
	public function getTemplatePath(): string {
		return $GLOBALS['wgStyleDirectory'] .
			'/BlueSpiceDiscovery/resources/templates/renderer/default-searchform';
	}
}
