<?php

namespace BlueSpice\HideTitle;

use MediaWiki\ResourceLoader\Context as ResourceLoaderContext;

interface ICssSelectorModule {

	/**
	 * Example:
	 * return [
	 *	'all' => [
	 *		'#firstHeading'
	 *	]
	 * ];
	 *
	 * @return array
	 */
	public function getSelectors(): array;

	/**
	 * @param ResourceLoaderContext $context
	 * @return bool
	 */
	public function skip( $context ): bool;
}
