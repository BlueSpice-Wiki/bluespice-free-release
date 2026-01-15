<?php

namespace MWStake\MediaWiki\Lib\Nodes;

abstract class Node implements INode {
	/** @var mixed */
	private $originalData;

	/**
	 * @param mixed $data
	 */
	public function __construct( $data ) {
		$this->originalData = $data;
	}

	/**
	 * @return mixed
	 */
	public function getOriginalData() {
		return $this->originalData;
	}
}
