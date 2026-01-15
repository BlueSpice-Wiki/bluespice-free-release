<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\ResourceLoader;

use MediaWiki\MediaWikiServices;
use MediaWiki\ResourceLoader\FileModule as ResourceLoaderFileModule;
use MWStake\MediaWiki\Component\CommonUserInterface\ComponentRendererFactory;

class ComponentRenderer extends ResourceLoaderFileModule {

	/**
	 *
	 * @return void
	 */
	public function getTemplates() {
		$templates = [];
		$services = MediaWikiServices::getInstance();
		/** @var ComponentRendererFactory */
		$rendererFactory = $services->get( 'MWStakeCommonUIComponentRendererFactory' );
		$renderers = $rendererFactory->getAllRenderers();
		foreach ( $renderers as $rendererKey => $renderer ) {
			// Keep aligned with `Setup::makeClientSideRendererConfig`
			$pathname = $renderer->getTemplatePathname();
			$templateKey = sha1( $pathname ) . '.mustache';
			$content = file_get_contents( $pathname );
			$templates[$templateKey] = $this->stripBom( $content );
		}

		return $templates;
	}
}
