<?php

namespace BlueSpice\Discovery\EnhancedSidebar;

use MWStake\MediaWiki\Lib\Nodes\INodeSource;

class JsonNodeSource implements INodeSource {

	/**
	 *
	 * @var array
	 */
	private $data;

	/**
	 *
	 * @param array $data
	 */
	public function __construct( array $data ) {
		$this->data = $data;
	}

	/**
	 *
	 * @return array
	 */
	public function getData(): array {
		return $this->data;
	}

}
