<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\Data\WebNotifications;

use Exception;
use MediaWiki\Extension\NotifyMe\Grouping\Grouper;
use MediaWiki\Extension\NotifyMe\Grouping\NotificationGroup;
use MediaWiki\Extension\NotifyMe\NotificationSerializer;
use MediaWiki\Extension\NotifyMe\Storage\NotificationStore;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\DataStore\ISecondaryDataProvider;
use MWStake\MediaWiki\Component\Events\Notification;
use Throwable;

class SecondaryDataProvider implements ISecondaryDataProvider {

	/**
	 * @var NotificationStore
	 */
	private $notificationStore;

	/**
	 * @var NotificationSerializer
	 */
	private $serializer;

	/**
	 * @var UserIdentity
	 */
	private $forUser;

	/**
	 * @var bool
	 */
	private $grouping;

	/** @var int */
	private $limit;

	/** @var int */
	private $processedCount = 0;

	/**
	 * @var int
	 */
	private $itemsCount = 0;

	/**
	 * @param NotificationStore $notificationStore
	 * @param NotificationSerializer $serializer
	 * @param UserIdentity $forUser
	 * @param bool $grouping
	 * @param int $limit
	 */
	public function __construct(
		NotificationStore $notificationStore, NotificationSerializer $serializer, UserIdentity $forUser,
		bool $grouping, int $limit
	) {
		$this->notificationStore = $notificationStore;
		$this->serializer = $serializer;
		$this->forUser = $forUser;
		$this->grouping = $grouping;
		$this->limit = $limit;
	}

	/**
	 * @inheritDoc
	 */
	public function extend( $dataSets ): array {
		$output = [];

		$batchSize = 50;
		$chunks = array_chunk( $dataSets, $batchSize );
		foreach ( $chunks as $chunk ) {
			if ( count( $output ) >= $this->limit ) {
				break;
			}

			$notifications = [];
			foreach ( $chunk as $dataSet ) {
				$id = $dataSet->get( WebNotificationRecord::ID );
				try {
					$notifications[] = $this->notificationStore->getNotification( $id );
				} catch ( Exception $e ) {
					// Notification not found
					continue;
				}
			}
			$this->addToOutput( $notifications, $output );
		}

		return [ $output, $this->itemsCount, $this->processedCount ];
	}

	/**
	 * @param array $notifications
	 * @param array &$output
	 * @return void
	 */
	private function addToOutput( array $notifications, array &$output ) {
		if ( $this->grouping ) {
			$grouper = new Grouper( $notifications );
			$grouped = $grouper->group();

			$this->itemsCount = count( $grouped );

			foreach ( $grouped as $notification ) {
				try {
					if ( $notification instanceof Notification ) {
						$this->processedCount++;
						$serialized = $this->serializer->serializeForOutput( $notification, $this->forUser );
						$output[] = new WebNotificationRecord( (object)$serialized );
						if ( count( $output ) === $this->limit ) {
							return;
						}
						continue;
					}
					if ( $notification instanceof NotificationGroup ) {
						$this->processedCount += count( $notification->getNotifications() );
						$serialized = $this->serializer->serializeNotificationGroupForOutput(
							$notification, $this->forUser
						);
						$output[] = new WebNotificationRecord( (object)$serialized );
						if ( count( $output ) === $this->limit ) {
							return;
						}
					}
				} catch ( Throwable $e ) {
					$this->processedCount++;
					// Skip
					continue;
				}
			}
		} else {
			$this->itemsCount = count( $notifications );

			foreach ( $notifications as $notification ) {
				if ( count( $output ) === $this->limit ) {
					return;
				}
				$this->processedCount++;
				try {
					if ( $notification instanceof Notification ) {
						$serialized = $this->serializer->serializeForOutput( $notification, $this->forUser );
						$output[] = new WebNotificationRecord( (object)$serialized );
						continue;
					}
				} catch ( Throwable $e ) {
					// Skip
					continue;
				}
			}
		}
	}

}
