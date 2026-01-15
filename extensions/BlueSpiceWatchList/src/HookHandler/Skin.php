<?php

namespace BlueSpice\WatchList\HookHandler;

use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;

class Skin implements SkinTemplateNavigation__UniversalHook {

	/**
	 * // phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
	 * @inheritDoc
	 */
	public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
		if ( !isset( $links['watchlist'] ) ) {
			return;
		}
		if ( !isset( $links['watchlist']['data'] ) ) {
			$links['watchlist']['data'] = [];
		}
		$links['watchlist']['data']['attentionindicator'] = 'watchlist';
	}

}
