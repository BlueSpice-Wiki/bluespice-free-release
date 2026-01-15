<?php

namespace MWStake\MediaWiki\Lib\Nodes;

interface IParser {
	/**
	 * @return INode[]
	 */
	public function parse(): array;
}
