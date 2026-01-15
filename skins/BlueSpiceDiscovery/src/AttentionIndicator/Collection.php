<?php

namespace BlueSpice\Discovery\AttentionIndicator;

use BlueSpice\Discovery\AttentionIndicator;
use BlueSpice\Discovery\AttentionIndicatorFactory;
use BlueSpice\Discovery\IAttentionIndicator;
use MediaWiki\Config\Config;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\User;

abstract class Collection extends AttentionIndicator {

	/**
	 * @var AttentionIndicatorFactory
	 */
	protected $attentionIndicatorFactory = null;

	/**
	 * @param string $key
	 * @param Config $config
	 * @param User $user
	 * @param AttentionIndicatorFactory $attentionIndicatorFactory
	 */
	public function __construct( string $key, Config $config, User $user,
		AttentionIndicatorFactory $attentionIndicatorFactory ) {
		parent::__construct( $key, $config, $user );
		$this->attentionIndicatorFactory = $attentionIndicatorFactory;
	}

	/**
	 * @param string $key
	 * @param Config $config
	 * @param User $user
	 * @param MediaWikiServices $services
	 * @param AttentionIndicatorFactory|null $attentionIndicatorFactory
	 * @return IAttentionIndicator
	 */
	public static function factory( string $key, Config $config, User $user,
		MediaWikiServices $services, ?AttentionIndicatorFactory $attentionIndicatorFactory = null ) {
		if ( !$attentionIndicatorFactory ) {
			$attentionIndicatorFactory = $services->getService( 'BSAttentionIndicatorFactory' );
		}
		return new static( $key, $config, $user, $attentionIndicatorFactory );
	}

	/**
	 * @return int
	 */
	protected function doIndicationCount(): int {
		$count = 0;
		foreach ( $this->getSubIndicatorKeys() as $key ) {
			$count += $this->attentionIndicatorFactory->get( $key, $this->user )
				->getIndicationCount();
		}
		return $count;
	}

	/**
	 * @return string[]
	 */
	abstract protected function getSubIndicatorKeys(): array;

}
