<?php

namespace MediaWiki\Extension\NotifyMe;

use Exception;
use MediaWiki\Extension\NotifyMe\Storage\NotificationStore;
use MediaWiki\User\UserFactory;
use MWStake\MediaWiki\Component\Events\BotAgent;
use MWStake\MediaWiki\Component\Events\ForcedEvent;
use MWStake\MediaWiki\Component\Events\INotificationEvent;
use MWStake\MediaWiki\Component\Events\INotificationEventConsumer;
use MWStake\MediaWiki\Component\ProcessManager\ManagedProcess;
use MWStake\MediaWiki\Component\ProcessManager\ProcessManager;
use Psr\Log\LoggerInterface;

/**
 * This class listens to events fired, and enqueues tasks for the NotificationsEmitter
 * to process and dispatch to channels
 */
final class NotificationEventConsumer implements INotificationEventConsumer {
	/** @var NotificationStore */
	private $store;
	/** @var ProcessManager */
	private $processManager;
	/** @var LoggerInterface */
	private $logger;
	/** @var EventProvider */
	private $eventProvider;
	/** @var UserFactory */
	private $userFactory;

	/**
	 * @param NotificationStore $store
	 * @param ProcessManager $processManager
	 * @param LoggerInterface $logger
	 * @param EventProvider $eventProvider
	 * @param UserFactory $userFactory
	 */
	public function __construct(
		NotificationStore $store, ProcessManager $processManager, LoggerInterface $logger,
		EventProvider $eventProvider, UserFactory $userFactory
	) {
		$this->store = $store;
		$this->processManager = $processManager;
		$this->logger = $logger;
		$this->eventProvider = $eventProvider;
		$this->userFactory = $userFactory;
	}

	/**
	 * @param INotificationEvent $event
	 *
	 * @return void
	 * @throws Exception
	 */
	public function consume( INotificationEvent $event ): void {
		$eventId = $this->store->persistEvent( $event );
		$this->logger->info( 'Emitting event ' . $event->getKey() . ', agent: ' . $event->getAgent()->getName() );
		$this->queueTask( $eventId );
	}

	/**
	 * @param INotificationEvent $event
	 *
	 * @return bool
	 */
	public function isInterested( INotificationEvent $event ): bool {
		$availableEvents = array_keys( $this->eventProvider->getRegisteredEvents() );
		if ( !$event->getAgent() instanceof BotAgent ) {
			$agentUser = $this->userFactory->newFromUserIdentity( $event->getAgent() );
			if ( $agentUser->isBot() ) {
				// Do not emit any events created by bots (unless they are created by designated NotificationsBot)
				return false;
			}
		}
		return $event instanceof ForcedEvent || in_array( $event->getKey(), $availableEvents );
	}

	/**
	 * @param int $eventId
	 *
	 * @return void
	 * @throws Exception
	 */
	private function queueTask( int $eventId ) {
		$process = new ManagedProcess( [
			'emit_notifications' => [
				'class' => NotificationEmitter::class,
				'args' => [ $eventId ],
				'services' => [
					'NotifyMe.Store', 'NotifyMe.Logger',
					'NotifyMe.SubscriberManager', 'NotifyMe.ChannelFactory', 'HookContainer'
				]
			]
		] );

		$pid = $this->processManager->startProcess( $process );
		if ( !$pid ) {
			throw new Exception( 'Failed to start process for the event' );
		}
		$this->logger->info( 'Started process for event ' . $eventId . ' with pid ' . $pid );
		$this->store->setEventProcess( $pid, $eventId );
	}
}
