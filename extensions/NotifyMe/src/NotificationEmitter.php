<?php

namespace MediaWiki\Extension\NotifyMe;

use Exception;
use MediaWiki\Extension\NotifyMe\Storage\NotificationStore;
use MediaWiki\HookContainer\HookContainer;
use MWStake\MediaWiki\Component\Events\Delivery\IExternalChannel;
use MWStake\MediaWiki\Component\Events\Delivery\NotificationStatus;
use MWStake\MediaWiki\Component\Events\INotificationEvent;
use MWStake\MediaWiki\Component\Events\Notification;
use MWStake\MediaWiki\Component\ProcessManager\IProcessStep;
use Psr\Log\LoggerInterface;

class NotificationEmitter implements IProcessStep {
	/** @var int */
	private $eventId;
	/**
	 * @var NotificationStore
	 */
	private $store;
	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/** @var ChannelFactory */
	private $channelFactory;
	/** @var SubscriberManager */
	private $subscriberManager;
	/** @var HookContainer */
	private $hookContainer;

	/**
	 * @param NotificationStore $store
	 * @param LoggerInterface $logger
	 * @param SubscriberManager $subscriberManager
	 * @param ChannelFactory $channelFactory
	 * @param HookContainer $hookContainer
	 * @param int $eventId
	 */
	public function __construct(
		NotificationStore $store, LoggerInterface $logger, SubscriberManager $subscriberManager,
		ChannelFactory $channelFactory, HookContainer $hookContainer, int $eventId
	) {
		$this->store = $store;
		$this->logger = $logger;
		$this->subscriberManager = $subscriberManager;
		$this->channelFactory = $channelFactory;
		$this->hookContainer = $hookContainer;

		$this->eventId = $eventId;
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 * @throws Exception
	 */
	public function execute( $data = [] ): array {
		try {
			$event = $this->store->getEvent( $this->eventId );
		} catch ( Exception $e ) {
			$this->logger->error( 'Failed to retrieve event {eventId}: {exception}', [
				'eventId' => $this->eventId,
				'exception' => $e->getMessage()
			] );
			return [];
		}

		// Generate notifications for event, one per subscriber and channel
		$notifications = $this->generateNotifications( $event );

		$this->logger->debug( 'Generated {count} notifications for event {event}', [
			'count' => count( $notifications ),
			'event' => $event->getKey()
		] );
		// Deliver notifications that support pushing
		foreach ( $notifications as $notification ) {
			try {
				$channel = $notification->getChannel();
				if ( $channel instanceof IExternalChannel && $channel->deliver( $notification ) ) {
					$this->logger->debug(
						'Notification for event {event} and user {user} delivered to {channelName}',
						[
							'event' => $event->getKey(),
							'user' => $notification->getTargetUser()->getName(),
							'channelName' => $channel->getKey()
						]
					);
					$notification->getStatus()->markAsCompleted();
				}
			} catch ( Exception $e ) {
				$this->logger->error(
					'Notification for event {event} and user {user} failed to deliver to {channelName}: {exception}',
					[
						'event' => $event->getKey(),
						'user' => $notification->getTargetUser()->getName(),
						'channelName' => $notification->getChannel()->getKey(),
						'exception' => $e->getMessage()
					]
				);
				$notification->getStatus()->markAsFailed( $e->getMessage() );

			}
			$this->store->persist( $notification, $this->eventId );
		}

		return [];
	}

	/**
	 * Generate notifications for a given event based on its subscribers
	 * Do not call unless you know what you are doing
	 *
	 * @param INotificationEvent $event
	 *
	 * @return Notification[]
	 * @throws Exception
	 */
	public function generateNotifications( INotificationEvent $event ): array {
		$notifications = [];
		foreach ( $this->channelFactory->getChannels() as $channel ) {
			$subscribers = $this->subscriberManager->getSubscribers( $event, $channel );
			$this->logger->debug( '{channel}: {subs}',
				[
					'subs' => count( $subscribers ),
					'channel' => $channel->getKey()
				]
			);
			foreach ( $subscribers as $userData ) {
				$prevent = false;
				$this->hookContainer->run(
					'NotifyMeBeforeGenerateNotification',
					[ $event, $userData['user'], $userData['providers'], &$prevent ]
				);
				if ( $prevent || $channel->shouldSkip( $event, $userData['user'] ) ) {
					$this->logger->info( 'Skipping notification for event {event} for user {user} on channel {channel}',
						[
							'event' => $event->getKey(),
							'user' => $userData['user']->getName(),
							'channel' => $channel->getKey()
						]
					);
					continue;
				}
				$notifications[] = new Notification(
					$event,
					$userData['user'],
					$channel,
					new NotificationStatus(),
					array_map( function ( $provider ) {
						return $this->subscriberManager->getProvider( $provider );
					}, $userData['providers'] )
				);
				$this->logger->debug( 'Generated notification for event {event} for user {user} on channel {channel}',
					[
						'event' => $event->getKey(),
						'user' => $userData['user']->getName(),
						'channel' => $channel->getKey()
					]
				);
			}
		}

		return $notifications;
	}
}
