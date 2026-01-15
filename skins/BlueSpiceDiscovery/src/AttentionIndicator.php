<?php

namespace BlueSpice\Discovery;

use MediaWiki\Config\Config;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\User;

abstract class AttentionIndicator implements IAttentionIndicator {

	/**
	 * @var string
	 */
	private $key = '';

	/**
	 * @var Config
	 */
	protected $config = null;

	/**
	 * @var User
	 */
	protected $user = null;

	/**
	 * @var int
	 */
	protected $indicationCount = null;

	/**
	 * @param string $key
	 * @param Config $config
	 * @param User $user
	 */
	public function __construct( string $key, Config $config, User $user ) {
		$this->key = $key;
		$this->config = $config;
		$this->user = $user;
	}

	/**
	 * @param string $key
	 * @param Config $config
	 * @param User $user
	 * @param MediaWikiServices $services
	 * @return IAttentionIndicator
	 */
	public static function factory( string $key, Config $config, User $user,
		MediaWikiServices $services ) {
		return new static( $key, $config, $user );
	}

	/**
	 * @return bool
	 */
	public function hasIndication(): bool {
		return $this->getIndicationCount() > 0;
	}

	/**
	 * @return int
	 */
	public function getIndicationCount(): int {
		if ( $this->indicationCount !== null ) {
			return $this->indicationCount;
		}
		$this->indicationCount = $this->doIndicationCount();
		return $this->indicationCount;
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return $this->key;
	}

	/**
	 * @return int
	 */
	abstract protected function doIndicationCount(): int;

}
