<?php

namespace BlueSpice\Discovery;

use MediaWiki\Hook\SkinAddFooterLinksHook;
use MediaWiki\HookContainer\HookContainer;
use Skin;

class HookRunner implements SkinAddFooterLinksHook {

	/** @var HookContainer */
	private $hookContainer;

	/**
	 *
	 * @param HookContainer $hookContainer
	 */
	public function __construct( HookContainer $hookContainer ) {
		$this->hookContainer = $hookContainer;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function onSkinAddFooterLinks( Skin $skin, string $key, array &$footerItems ) {
		return $this->hookContainer->run(
			'SkinAddFooterLinks',
			[ $skin, $key, &$footerItems ]
		);
	}
}
