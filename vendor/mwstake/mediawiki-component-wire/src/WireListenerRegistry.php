<?php

namespace MWStake\MediaWiki\Component\Wire;

use MediaWiki\HookContainer\HookContainer;
use MWStake\MediaWiki\Component\Wire\Listener\WireListener;
use MWStake\MediaWiki\Component\Wire\Listener\WireListenerOnPage;
use Wikimedia\ObjectFactory\ObjectFactory;

class WireListenerRegistry {

	/** @var array|null */
	private ?array $listeners = null;

	/**
	 * @param array $staticListeners
	 * @param HookContainer $hookContainer
	 * @param ObjectFactory $objectFactory
	 */
	public function __construct(
		private readonly array $staticListeners,
		private readonly HookContainer $hookContainer,
		private readonly ObjectFactory $objectFactory
	) {
	}

	/**
	 * @param WireListener $listener
	 * @return void
	 */
	public function register( WireListener $listener ) {
		$this->assertLoaded();
		$this->listeners[] = $listener;
	}

	/**
	 * @param WireChannel $channel
	 * @return WireListener[]
	 */
	public function getListeners( WireChannel $channel ): array {
		$this->assertLoaded();
		$subscribed = [];
		foreach ( $this->listeners as $listener ) {
			if ( $listener instanceof WireListenerOnPage ) {
				if ( !str_starts_with( $channel->getKey(), 'page-' ) ) {
					continue;
				}
				$page = $listener->getPageToListenOn();
				if ( $page ) {
					$requiredChannel = ( new WireChannelFactory() )->getChannelForPage( $page );
					if ( $requiredChannel->getKey() !== $channel->getKey() ) {
						continue;
					}
				}
				$subscribed[] = $listener;
			} else {
				$subscribed[] = $listener;
			}
		}

		return $subscribed;
	}

	/**
	 * @return void
	 */
	private function assertLoaded(): void {
		if ( $this->listeners === null ) {
			$this->listeners = [];
			foreach ( $this->staticListeners as $listener ) {
				$object = $this->objectFactory->createObject( $listener );
				if ( !( $object instanceof WireListener ) ) {
					throw new \RuntimeException(
						"Object $listener is not an instance of " . WireListener::class
					);
				}
				$this->listeners[] = $object;
			}
			$this->hookContainer->run( 'MWStakeWireListeners', [ $this ] );
		}
	}
}
