<?php

namespace MediaWiki\Extension\MenuEditor\Parser;

interface IMenuParser {

	/**
	 * Add individual nodes from batch data
	 *
	 * @param array $nodes
	 * @param bool $replace
	 *
	 * @return void
	 */
	public function addNodesFromData( array $nodes, bool $replace = false );
}
