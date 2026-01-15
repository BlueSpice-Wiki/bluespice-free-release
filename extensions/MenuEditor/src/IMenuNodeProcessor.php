<?php

namespace MediaWiki\Extension\MenuEditor;

use MWStake\MediaWiki\Lib\Nodes\INodeProcessor;

interface IMenuNodeProcessor extends INodeProcessor {
	/**
	 * @param string $wikitext
	 * @return bool
	 */
	public function matches( $wikitext ): bool;
}
