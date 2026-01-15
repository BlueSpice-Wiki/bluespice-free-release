<?php

namespace MWStake\MediaWiki\Lib\Nodes;

interface IMutableNode extends INode {
	/**
	 * Get data after any possible mutations
	 *
	 * @return mixed
	 */
	public function getCurrentData();
}
