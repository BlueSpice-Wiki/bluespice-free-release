<?php
namespace BS\UsageTracker\Collectors;

use BS\UsageTracker\CollectorResult;

class Basic extends Base {

	/** @var int */
	protected $count = 0;

	/**
	 * @param array $config
	 */
	public function __construct( $config = [] ) {
		parent::__construct( $config );
		if ( isset( $config['config']['count'] ) ) {
			$this->count = $config['config']['count'];
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getUsageData() {
		$res = new CollectorResult( $this );
		$res->count = $this->count;

		return $res;
	}
}
