<?php

namespace BlueSpice\UserSidebar;

use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;

abstract class Widget implements IWidget {

	/**
	 *
	 * @var string
	 */
	protected $key = '';

	/**
	 *
	 * @var IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @var array
	 */
	protected $params = [];

	/**
	 * @param string $key
	 * @param IContextSource $context
	 * @param Config $config
	 * @param array $params
	 */
	protected function __construct( string $key, IContextSource $context, Config $config,
		array $params ) {
		$this->key = $key;
		$this->config = $config;
		$this->context = $context;
		$this->params = $params;
	}

	/**
	 * @param string $key
	 * @param IContextSource $context
	 * @param Config $config
	 * @param array $params
	 * @return IWidget
	 */
	public static function factory( string $key, IContextSource $context, Config $config,
		array $params = [] ) {
		return new static( $key, $context, $config, $params );
	}

	/**
	 *
	 * @return string
	 */
	public function getKey(): string {
		return $this->key;
	}

}
