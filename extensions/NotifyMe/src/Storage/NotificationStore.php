<?php

namespace MediaWiki\Extension\NotifyMe\Storage;

use Exception;
use MediaWiki\Extension\NotifyMe\EventProvider;
use MediaWiki\Extension\NotifyMe\NotificationSerializer;
use MediaWiki\User\User;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;
use MWStake\MediaWiki\Component\Events\Delivery\NotificationStatus;
use MWStake\MediaWiki\Component\Events\INotificationEvent;
use MWStake\MediaWiki\Component\Events\Notification;
use Wikimedia\Rdbms\ILoadBalancer;

class NotificationStore {
	/** @var ILoadBalancer */
	private $loadBalancer;
	/** @var NotificationSerializer */
	private $serializer;

	/** @var EventProvider */
	private $eventProvider;

	/** @var array */
	private $conditions = [];

	/**
	 * @param ILoadBalancer $loadBalancer
	 * @param NotificationSerializer $serializer
	 * @param EventProvider $eventProvider
	 */
	public function __construct(
		ILoadBalancer $loadBalancer, NotificationSerializer $serializer, EventProvider $eventProvider
	) {
		$this->loadBalancer = $loadBalancer;
		$this->serializer = $serializer;
		$this->eventProvider = $eventProvider;
	}

	/**
	 * @param int $id
	 *
	 * @return INotificationEvent
	 * @throws Exception
	 */
	public function getEvent( int $id ): INotificationEvent {
		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		$row = $dbr->selectRow(
			'notifications_event',
			[
				'ne_key', 'ne_timestamp', 'ne_payload'
			],
			[ 'ne_id' => $id ],
			__METHOD__
		);

		if ( !$row ) {
			throw new Exception( "Event with id $id not found" );
		}

		return $this->serializer->unserializeEvent( $row );
	}

	/**
	 * @param Notification $notification
	 * @param int|null $eventId
	 *
	 * @return void
	 * @throws Exception
	 */
	public function persist( Notification $notification, ?int $eventId = null ) {
		if ( $notification->getId() === null ) {
			if ( !$eventId ) {
				throw new Exception( 'Event id is required for new notifications' );
			}
			$this->insert( $notification, $eventId );
			$notification->getChannel()->onNotificationPersisted( $notification, true );
			return;
		}
		$this->update( $notification );
		$notification->getChannel()->onNotificationPersisted( $notification, false );
	}

	/**
	 * @param IChannel|string $channel Channel object or key
	 *
	 * @return $this
	 */
	public function forChannel( $channel ): self {
		if ( $channel instanceof IChannel ) {
			$channel = $channel->getKey();
		}
		$this->conditions['ni_channel'] = $channel;
		return $this;
	}

	/**
	 * Query helper
	 * @return $this
	 */
	public function pending(): NotificationStore {
		$this->conditions['ni_status'] = NotificationStatus::STATUS_PENDING;
		return $this;
	}

	/**
	 * Query helper
	 *
	 * @param User $user
	 *
	 * @return $this
	 */
	public function forUser( User $user ): NotificationStore {
		$this->conditions['ni_target_user'] = $user->getId();
		return $this;
	}

	/**
	 * Query helper
	 * @return $this
	 */
	public function delivered(): NotificationStore {
		$this->conditions['ni_status'] = NotificationStatus::STATUS_COMPLETED;
		return $this;
	}

	/**
	 * Get single notification by ID
	 *
	 * @param int $id
	 *
	 * @return Notification
	 * @throws Exception
	 */
	public function getNotification( int $id ): Notification {
		$res = $this->query( [ 'ni_id' => $id ] );
		if ( !$res ) {
			throw new Exception( 'Notification not found' );
		}
		$notification = $res[0];
		if ( !$this->isValidEventType( $notification->getEvent()->getKey() ) ) {
			throw new Exception( 'Invalid event type' );
		}
		return $notification;
	}

	/**
	 * @param array $conds
	 *
	 * @return Notification[]
	 */
	public function query( $conds = [] ): array {
		$dbr = $this->loadBalancer->getConnection( ILoadBalancer::DB_REPLICA );
		$res = $dbr->select(
			[ 'notifications_instance', 'notifications_event' ],
			[ 'ni_id', 'ni_event_type', 'ne_key', 'ne_id', 'ne_timestamp', 'ni_channel', 'ni_payload', 'ne_payload' ],
			array_merge( $this->conditions, $conds ),
			__METHOD__,
			[], [
				'notifications_event' => [
					'LEFT JOIN', 'ni_event_id = ne_id'
				]
			]
		);

		$notifications = [];
		foreach ( $res as $row ) {
			try {
				if ( !$this->isValidEventType( $row->ni_event_type ) ) {
					continue;
				}
				$notifications[] = $this->serializer->unserialize( $row );
			} catch ( Exception $e ) {
				// Skip invalid notifications
				continue;
			}
		}

		$this->conditions = [];
		return $notifications;
	}

	/**
	 * @param INotificationEvent $event
	 *
	 * @return int
	 * @throws Exception
	 */
	public function persistEvent( INotificationEvent $event ): int {
		$dbw = $this->loadBalancer->getConnection( ILoadBalancer::DB_PRIMARY );
		$res = $dbw->insert(
			'notifications_event',
			[
				'ne_key' => $event->getKey(),
				'ne_agent' => $event->getAgent()->getId(),
				'ne_timestamp' => $event->getTime()->format( 'YmdHis' ),
				'ne_payload' => json_encode( $this->serializer->serializeEvent( $event ) ),
			],
			__METHOD__
		);

		if ( $res && $dbw->insertId() ) {
			return $dbw->insertId();
		}

		throw new Exception( 'Failed to insert event' );
	}

	/**
	 * @param string $processId
	 * @param int $eventId
	 *
	 * @return void
	 */
	public function setEventProcess( string $processId, int $eventId ) {
		$dbw = $this->loadBalancer->getConnection( ILoadBalancer::DB_PRIMARY );
		$dbw->update(
			'notifications_event',
			[
				'ne_process' => $processId,
				'ne_process_result' => 'active'
			],
			[
				'ne_id' => $eventId,
			],
			__METHOD__
		);
	}

	/**
	 * @param int $eventId
	 * @param string $status
	 *
	 * @return void
	 */
	public function updateEventProcessStatus( int $eventId, string $status ) {
		$dbw = $this->loadBalancer->getConnection( DB_PRIMARY );
		$dbw->update(
			'notifications_event',
			[
				'ne_process_result' => $status,
			],
			[
				'ne_id' => $eventId,
			],
			__METHOD__
		);
	}

	/**
	 * Get process IDs for events that are still active
	 * @return array
	 */
	public function getPendingEventProcesses(): array {
		$dbr = $this->loadBalancer->getConnection( ILoadBalancer::DB_REPLICA );
		$res = $dbr->select(
			'notifications_event',
			[ 'ne_id', 'ne_process', 'ne_process_result' ],
			[
				'ne_process_result' => 'active'
			],
			__METHOD__
		);

		$processes = [];
		foreach ( $res as $row ) {
			$processes[] = [
				'id' => (int)$row->ne_id,
				'process' => $row->ne_process,
				'status' => $row->ne_process_result
			];
		}

		return $processes;
	}

	/**
	 * @param Notification $notification
	 *
	 * @return void
	 * @throws Exception
	 */
	private function update( Notification $notification ) {
		$payload = $this->serializer->serialize( $notification );
		$payload = json_encode( $payload );

		$dbw = $this->loadBalancer->getConnection( DB_PRIMARY );
		$res = $dbw->update(
			'notifications_instance',
			[
				'ni_status' => $notification->getStatus()->getStatus(),
				'ni_payload' => $payload,
			],
			[ 'ni_id' => $notification->getId() ],
			__METHOD__
		);

		if ( !$res ) {
			throw new Exception( 'Failed to update notification' );
		}
	}

	/**
	 * @param Notification $notification
	 * @param int $eventId
	 *
	 * @return void
	 * @throws Exception
	 */
	private function insert( Notification $notification, int $eventId ) {
		if ( $notification->getId() !== null ) {
			throw new Exception( 'Notification already exists' );
		}
		$payload = $this->serializer->serialize( $notification );
		$payload = json_encode( $payload );

		$dbw = $this->loadBalancer->getConnection( DB_PRIMARY );
		$res = $dbw->insert(
			'notifications_instance',
			[
				'ni_event_id' => $eventId,
				'ni_event_type' => $notification->getEvent()->getKey(),
				'ni_target_user' => $notification->getTargetUser()->getId(),
				'ni_channel' => $notification->getChannel()->getKey(),
				'ni_status' => $notification->getStatus()->getStatus(),
				'ni_payload' => $payload,
			],
			__METHOD__
		);

		if ( $res && $dbw->insertId() ) {
			$notification->setId( $dbw->insertId() );
		} else {
			throw new Exception( 'Failed to insert notification' );
		}
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	private function isValidEventType( string $type ): bool {
		$registered = array_keys( $this->eventProvider->getRegisteredEvents() );
		return in_array( $type, $registered );
	}
}
