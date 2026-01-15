<?php

namespace BlueSpice\Discovery;

use MediaWiki\Config\GlobalVarConfig;

class Config extends GlobalVarConfig {

	/**
	 *
	 * Default builder function
	 * @return Config
	 */
	public static function newInstance() {
		return new static();
	}

	public function __construct() {
		parent::__construct( 'bsg' );
	}
}
