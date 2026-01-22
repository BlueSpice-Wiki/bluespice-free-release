<?php

namespace MediaWiki\Extension\NotifyMe;

use MediaWiki\HookContainer\HookContainer;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;
use Wikimedia\ObjectFactory\ObjectFactory;

class ChannelFactory {
	/**
	 * @var IChannel[]|null
	 */
	private $channels = null;
	/** @var array */
	private $attribute;
	/** @var ObjectFactory */
	private $objectFactory;
	/** @var HookContainer */
	private $hookContainer;

	/**
	 * @param array $attribute
	 * @param ObjectFactory $objectFactory
	 * @param HookContainer $hookContainer
	 */
	public function __construct( array $attribute, ObjectFactory $objectFactory, HookContainer $hookContainer ) {
		$this->attribute = $attribute;
		$this->objectFactory = $objectFactory;
		$this->hookContainer = $hookContainer;
	}

	/**
	 * @param IChannel $channel
	 * @param bool $overwrite Whether to overwrite an existing channel with the same key
	 *
	 * @return bool False if channel already exists
	 */
	public function registerChannel( IChannel $channel, $overwrite = false ): bool {
		$this->assertLoaded();
		if ( !$overwrite && isset( $this->channels[$channel->getKey()] ) ) {
			return false;
		}
		$this->channels[$channel->getKey()] = $channel;
		return true;
	}

	/**
	 * @param string $key
	 * @return IChannel|null
	 */
	public function getChannel( string $key ): ?IChannel {
		$this->assertLoaded();
		return $this->channels[$key] ?? null;
	}

	/**
	 * @return IChannel[]
	 */
	public function getChannels(): array {
		$this->assertLoaded();
		return $this->channels;
	}

	private function assertLoaded() {
		if ( $this->channels === null ) {
			$this->channels = [];
			$this->loadChannels();
		}
	}

	private function loadChannels() {
		foreach ( $this->attribute as $key => $spec ) {
			$channel = $this->objectFactory->createObject( $spec );
			if ( !$channel instanceof IChannel ) {
				throw new \RuntimeException( "Channel $key does not implement IChannel" );
			}
			$this->registerChannel( $channel );
		}
		$this->hookContainer->run( 'NotifyMeRegisterChannel', [ $this ] );
	}
}
