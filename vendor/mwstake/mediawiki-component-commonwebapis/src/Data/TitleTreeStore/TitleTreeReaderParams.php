<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleTreeStore;

class TitleTreeReaderParams extends \MWStake\MediaWiki\Component\DataStore\ReaderParams {

	/** @var string */
	private $node = '';
	/** @var array */
	private $expandPaths = [];

	/**
	 * @param array $params
	 */
	public function __construct( $params = [] ) {
		parent::__construct( $params );
		$this->setIfAvailable( $this->node, $params, 'node' );
		$this->setIfAvailable( $this->expandPaths, $params, 'expand-paths' );
	}

	/**
	 * @return string
	 */
	public function getNode(): string {
		return $this->node;
	}

	/**
	 * @return string[]
	 */
	public function getExpandPaths(): array {
		if ( $this->expandPaths ) {
			return json_decode( $this->expandPaths, 1 );
		}
		return [];
	}

	/**
	 * @return string
	 */
	public function getHash(): string {
		return md5( parent::getHash() . json_encode( [
			'node' => $this->node,
			'expand-paths' => $this->expandPaths,
		] ) );
	}
}
