<?php

namespace MediaWiki\Extension\NotifyMe\Channel;

use MediaWiki\Extension\NotifyMe\Storage\WebNotificationQueryStore;
use MediaWiki\Message\Message;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;
use MWStake\MediaWiki\Component\Events\INotificationEvent;
use MWStake\MediaWiki\Component\Events\Notification;
use Psr\Log\LoggerInterface;

class WebChannel implements IChannel {

	/** @var WebNotificationQueryStore */
	private $queryStore;

	/** @var LoggerInterface */
	private $logger;

	/**
	 * @param WebNotificationQueryStore $queryStore
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		WebNotificationQueryStore $queryStore, LoggerInterface $logger
	) {
		$this->queryStore = $queryStore;
		$this->logger = $logger;
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'web';
	}

	/**
	 * @return Message
	 */
	public function getLabel(): Message {
		return Message::newFromKey( 'notifyme-channel-web' );
	}

	/**
	 * @param INotificationEvent $event
	 * @param UserIdentity $user
	 *
	 * @return bool
	 */
	public function shouldSkip( INotificationEvent $event, UserIdentity $user ): bool {
		// Web notifications are always sent
		return false;
	}

	/**
	 * @return string[]
	 */
	public function getDefaultConfiguration(): array {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function onNotificationPersisted( Notification $notification, bool $created ): void {
		// Add data to the query store, to enable efficient querying
		if ( $created ) {
			if ( !$this->queryStore->add( $notification ) ) {
				$this->logger->error(
					'Failed to add notification to web query store',
					[
						'notification' => $notification->getId(),
						'user' => $notification->getTargetUser()->getName()
					]
				);
			}
		} else {
			if ( !$this->queryStore->update( $notification ) ) {
				$this->logger->error(
					'Failed to update notification in web query store',
					[
						'notification' => $notification->getId(),
						'user' => $notification->getTargetUser()->getName()
					]
				);
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onNotificationOutputSerialized( Notification $notification, array &$data ): void {
		// No-op
	}
}
