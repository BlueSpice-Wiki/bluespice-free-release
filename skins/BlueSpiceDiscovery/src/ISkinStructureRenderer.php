<?php

namespace BlueSpice\Discovery;

use MediaWiki\Context\IContextSource;

interface ISkinStructureRenderer {

	/**
	 *
	 * @param IContextSource $context
	 * @return string
	 */
	public function getHtml( $context ): string;
}
