<?php

namespace BlueSpice\Discovery\Structure;

use BlueSpice\Discovery\ISkinLayout;
use BlueSpice\Discovery\ISkinLayoutAware;
use MediaWiki\Context\IContextSource;
use MediaWiki\Message\Message;
use MediaWiki\Registration\ExtensionRegistry;

class SkipTo extends SkinStructureBase implements ISkinLayoutAware {

	/**
	 * @var ISkinLayout
	 */
	private $layout = null;

	/**
	 *
	 * @return string
	 */
	public function getName(): string {
		return 'skip-to';
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
	 * Parse templates recursive
	 *
	 * @return bool
	 */
	public function enableRecursivePartials(): bool {
		return false;
	}

	/**
	 *
	 * @return array
	 */
	public function getParams(): array {
		return [
			'aria-label' => Message::newFromKey( 'bs-discovery-skip-links-navigation-aria-label' )->text(),
			'body' => $this->buildList()
		];
	}

	/**
	 *
	 * @return string
	 */
	private function buildList(): string {
		$html = '<ul>';
		$attribute = ExtensionRegistry::getInstance()->getAttribute(
			'BlueSpiceDiscoverySkipToRegistry'
		);
		$skipToRegistry = $attribute[$this->layout->getName()];
		foreach ( $skipToRegistry as $name => $skipToItem ) {
			$html .= '<li><a class="mw-jump-link" href="#' . $skipToItem['id'] . '">';
			$html .= Message::newFromKey( $skipToItem['msg'] )->text();
			$html .= '</a></li>';
		}
		$html .= '</ul>';
		return $html;
	}

	/**
	 * @return array
	 */
	public function getStyles(): array {
		return [ 'skin.discovery.skip-links.styles' ];
	}

	/**
	 * @param ISkinLayout $layout
	 * @return void
	 */
	public function setSkinLayout( ISkinLayout $layout ): void {
		$this->layout = $layout;
	}
}
