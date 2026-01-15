<?php

namespace MWStake\MediaWiki\Lib\Nodes;

abstract class MutableNode extends Node implements IMutableNode {
	/** @var mixed */
	private $mutatedData;

	/**
	 * @param string $data
	 */
	public function __construct( $data ) {
		parent::__construct( $data );
		$this->mutatedData = $data;
	}

	/**
	 * @param mixed $data
	 */
	public function setData( $data ) {
		$this->mutatedData = $data;
	}

	/**
	 * @return string
	 */
	public function getCurrentData(): string {
		return $this->mutatedData;
	}
}
