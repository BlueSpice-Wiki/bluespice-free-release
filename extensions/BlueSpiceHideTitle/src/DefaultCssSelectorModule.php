<?php

namespace BlueSpice\HideTitle;

use MediaWiki\ResourceLoader\Context as ResourceLoaderContext;

class DefaultCssSelectorModule implements ICssSelectorModule {

	/**
	 * @return array
	 */
	public function getSelectors(): array {
		return [
			'all' => [
				'#firstHeading',
				'#content h1.firstHeading',
				'#contentSub',
				'#siteNotice'
			]
		];
	}

	/**
	 * @param ResourceLoaderContext $context
	 * @return bool
	 */
	public function skip( $context ): bool {
		return false;
	}
}
