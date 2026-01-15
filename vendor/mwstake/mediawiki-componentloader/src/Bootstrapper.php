<?php

namespace MWStake\MediaWiki\ComponentLoader;

use UnexpectedValueException;

class Bootstrapper {

	/**
	 * @var Bootstrapper
	 */
	private static $instance = null;

	private $registry = [];

	private $alreadyInitialized = false;

	private function __construct() {
	}

	/**
	 *
	 * @return Bootstrapper
	 */
	public static function getInstance() {
		if ( static::$instance === null ) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 *
	 * @return void
	 */
	public function init() {
		if ( $this->alreadyInitialized ) {
			return;
		}

		foreach ( $this->registry as $regKey => $callback ) {
			call_user_func( $callback );
		}
		$this->alreadyInitialized = true;
	}

	/**
	 *
	 * @param string $regKey
	 * @param callable $callback
	 * @return void
	 */
	public function register( $regKey, $callback ) {
		if ( isset( $this->registry[$regKey] ) ) {
			throw new UnexpectedValueException(
				"Component with key '$regKey' already registered!"
			);
		}

		$this->registry[$regKey] = $callback;
	}
}
