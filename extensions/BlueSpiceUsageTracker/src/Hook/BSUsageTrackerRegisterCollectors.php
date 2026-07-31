<?php

namespace BS\UsageTracker\Hook;

use BlueSpice\Hook;
use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;

abstract class BSUsageTrackerRegisterCollectors extends Hook {

	/**
	 * @var array
	 */
	protected $collectorConfig = null;

	/**
	 * @param array &$collectorConfig
	 * @return bool
	 */
	public static function callback( &$collectorConfig ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$collectorConfig
		);
		return $hookHandler->process();
	}

	/**
	 * @param IContextSource $context
	 * @param Config $config
	 * @param array &$collectorConfig
	 * @return bool
	 */
	public function __construct( $context, $config, &$collectorConfig ) {
		parent::__construct( $context, $config );

		$this->collectorConfig =& $collectorConfig;
	}
}
