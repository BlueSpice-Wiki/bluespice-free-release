<?php

namespace MWStake\MediaWiki\Lib\Nodes;

use JsonSerializable;

interface INode extends JsonSerializable {
	/**
	 * @return string
	 */
	public function getType(): string;

	/**
	 * Get raw data of the node
	 *
	 * @return mixed
	 */
	public function getOriginalData();
}
