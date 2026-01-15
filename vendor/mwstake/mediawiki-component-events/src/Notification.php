<?php

namespace MWStake\MediaWiki\Component\Events;

use MediaWiki\User\User;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;
use MWStake\MediaWiki\Component\Events\Delivery\NotificationStatus;

final class Notification {
	/** @var INotificationEvent */
	private $event;
	/** @var User */
	private $targetUser;
	/** @var IChannel */
	private $channel;
	/** @var NotificationStatus */
	private $status;
	/** @var int|null */
	private $id = null;
	/** @var array */
	private $sourceProviders;

	/**
	 * @param INotificationEvent $event
	 * @param User $targetUser
	 * @param IChannel $channel
	 * @param NotificationStatus $status
	 * @param array $sourceProviders
	 */
	public function __construct(
		INotificationEvent $event, User $targetUser, IChannel $channel,
		NotificationStatus $status, array $sourceProviders
	) {
		$this->event = $event;
		$this->targetUser = $targetUser;
		$this->channel = $channel;
		$this->status = $status;
		$this->sourceProviders = $sourceProviders;
	}

	/**
	 * @param int $id
	 *
	 * @return void
	 */
	public function setId( int $id ) {
		$this->id = $id;
	}

	/**
	 * @return int|null
	 */
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * @return INotificationEvent
	 */
	public function getEvent(): INotificationEvent {
		return $this->event;
	}

	/**
	 * @return User
	 */
	public function getTargetUser(): User {
		return $this->targetUser;
	}

	/**
	 * @return IChannel
	 */
	public function getChannel(): IChannel {
		return $this->channel;
	}

	/**
	 * @return NotificationStatus
	 */
	public function getStatus(): NotificationStatus {
		return $this->status;
	}

	/**
	 * Providers that provided the user to this notification
	 *
	 * @return array
	 */
	public function getSourceProviders(): array {
		return $this->sourceProviders;
	}
}
