<?php

namespace BlueSpice\Discovery\Structure;

use BlueSpice\Discovery\Component\BackToTopButton;
use MediaWiki\Context\IContextSource;

class BackToTop extends SkinStructureBase {

	/**
	 *
	 * @return string
	 */
	public function getName(): string {
		return 'back-to-top';
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool {
		return true;
	}

	/**
	 * @return array
	 */
	public function getParams(): array {
		$component = new BackToTopButton();
		$html = $this->componentRenderer->getComponentHtml( $component, $this->componentProcessData );
		return [
			'body' => $html
		];
	}

	/**
	 * @return array
	 */
	public function getStyles(): array {
		return [ 'skin.discovery.back-to-top.styles' ];
	}

	/**
	 * @return array
	 */
	public function getScripts(): array {
		return [ 'skin.discovery.back-to-top.scripts' ];
	}
}
