<?php

namespace MediaWiki\Extension\NotifyMe;

use InvalidArgumentException;
use MediaWiki\Extension\NotifyMe\Event\NullEvent;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\INotificationEvent;
use Throwable;
use Wikimedia\ObjectFactory\ObjectFactory;

class EventFactory {

	/**
	 * @var array
	 */
	private $serializableSpecs = [];

	/**
	 * @var array
	 */
	private $eventProvider;

	/**
	 * @var array
	 */
	private $eventSpecs = null;

	/**
	 * @var ObjectFactory
	 */
	private $objectFactory;

	/**
	 * @param EventProvider $eventProvider
	 * @param ObjectFactory $objectFactory The object factory.
	 */
	public function __construct( EventProvider $eventProvider, ObjectFactory $objectFactory ) {
		$this->eventProvider = $eventProvider;
		$this->objectFactory = $objectFactory;
	}

	/**
	 * @param string $event
	 * @return bool
	 */
	public function hasEvent( string $event ): bool {
		$this->assertSpecs();
		return isset( $this->eventSpecs[$event] );
	}

	/**
	 * Creates a notification event based on the specified key and arguments.
	 *
	 * @param string $key The key to identify the event.
	 * @param array $args The arguments to be passed to the event.
	 * @return INotificationEvent The created notification event.
	 * @throws InvalidArgumentException if the event is not registered, or if the 'spec' is missing
	 * 	in the event specifications, or if the object created does not implement the INotificationEvent interface.
	 * @throws InvalidArgumentException if an error occurs during the creation of the event object.
	 */
	public function create( string $key, array $args ): INotificationEvent {
		$spec = $this->getSpec( $key );
		if ( !$spec ) {
			// Fire event that will be emitted into the void
			return new NullEvent();
		}
		$spec['args'] = array_merge( $spec['args'] ?? [], $args );

		try {
			$event = $this->objectFactory->createObject( $spec );
			if ( !$event instanceof INotificationEvent ) {
				throw new InvalidArgumentException( 'Object created does not implement ' . INotificationEvent::class );
			}

			// Store specs used to create events, to use them as serializable data later on
			$eventHash = md5( $event->getKey() . spl_object_id( $event ) );
			$this->serializableSpecs[$eventHash] = $spec;
		} catch ( Throwable $ex ) {
			throw new InvalidArgumentException( $ex->getMessage() );
		}

		return $event;
	}

	/**
	 * If Event is created through factory, store its spec as serialization data
	 * Enabled more complex classes by not serializing complex objects and services
	 * @param INotificationEvent $event
	 * @return array|null
	 */
	public function getSerializableSpec( INotificationEvent $event ): ?array {
		$eventHash = md5( $event->getKey() . spl_object_id( $event ) );
		return $this->serializableSpecs[$eventHash] ?? null;
	}

	/**
	 * @return array
	 */
	public function getEventSpecs(): array {
		$this->assertSpecs();
		return $this->eventSpecs;
	}

	/**
	 * @param string $key
	 * @return string
	 */
	private function getClassForKey( string $key ): string {
		$spec = $this->getSpec( $key );
		if ( !isset( $spec['class'] ) ) {
			throw new InvalidArgumentException( 'Cannot determine inheritance, `class` missing' );
		}
		return $spec['class'];
	}

	/**
	 * @param string $key
	 * @return array|null
	 */
	private function getSpec( string $key ): ?array {
		$this->assertSpecs();
		if ( !$this->hasEvent( $key ) ) {
			return null;
		}
		if ( !isset( $this->eventSpecs[$key]['spec'] ) ) {
			throw new InvalidArgumentException( 'Event cannot be created over factory, `spec` missing' );
		}
		return $this->eventSpecs[$key]['spec'];
	}

	/**
	 * Retrieves a test event based on the given parameters.
	 *
	 * @param string $key The key used to retrieve the test event.
	 * @param UserIdentity $agent The agent associated with the test event.
	 * @param MediaWikiServices $services The MediaWiki services object.
	 * @param array $extraArgs Additional arguments for the test event.
	 * @return INotificationEvent The test event object.
	 */
	public function getTestEvent(
		string $key, UserIdentity $agent, MediaWikiServices $services, array $extraArgs
	): INotificationEvent {
		$class = $this->getClassForKey( $key );
		$args = call_user_func_array( [ $class, 'getArgsForTesting' ], [ $agent, $services, $extraArgs ] );
		return $this->create( $key, $args );
	}

	private function assertSpecs() {
		if ( $this->eventSpecs === null ) {
			$this->eventSpecs = $this->eventProvider->getRegisteredEvents();
		}
	}
}
