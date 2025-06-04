<?php

namespace BlueSpice\Avatars;

use MediaWiki\Config\Config;

abstract class AvatarGenerator implements IAvatarGenerator {
	/**
	 *
	 * @var string
	 */
	protected $name = '';
	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @param string $name
	 * @param Config $config
	 */
	protected function __construct( $name, Config $config ) {
		$this->name = $name;
		$this->config = $config;
	}

	/**
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 *
	 * @param string $name
	 * @param Config $config
	 * @return IAvatarGenerator
	 */
	public static function factory( $name, Config $config ) {
		return new static( $name, $config );
	}
}
