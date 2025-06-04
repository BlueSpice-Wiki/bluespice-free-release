<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\Data\WebNotifications;

use BadMethodCallException;
use JakubOnderka\PhpParallelLint\IWriter;
use MediaWiki\Extension\NotifyMe\NotificationSerializer;
use MediaWiki\Extension\NotifyMe\Storage\NotificationStore;
use MediaWiki\Extension\NotifyMe\Storage\WebNotificationQueryStore;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\DataStore\IReader;
use MWStake\MediaWiki\Component\DataStore\IStore;
use Wikimedia\Rdbms\ILoadBalancer;

class Store implements IStore {

	/**
	 * @var NotificationStore
	 */
	private $notificationStore;

	/**
	 * @var WebNotificationQueryStore
	 */
	private $queryStore;

	/**
	 * @var ILoadBalancer
	 */
	private $lb;

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

	/**
	 * @param NotificationStore $notificationStore
	 * @param WebNotificationQueryStore $queryStore
	 * @param ILoadBalancer $lb
	 * @param NotificationSerializer $serializer
	 * @param UserIdentity $forUser
	 * @param bool $grouping
	 */
	public function __construct(
		NotificationStore $notificationStore, WebNotificationQueryStore $queryStore,
		ILoadBalancer $lb, NotificationSerializer $serializer, UserIdentity $forUser, bool $grouping
	) {
		$this->notificationStore = $notificationStore;
		$this->queryStore = $queryStore;
		$this->lb = $lb;
		$this->serializer = $serializer;
		$this->forUser = $forUser;
		$this->grouping = $grouping;
	}

	/**
	 * @return WebNotificationSchema
	 */
	public function getSchema() {
		return new WebNotificationSchema();
	}

	/**
	 * @return IReader
	 */
	public function getReader() {
		return new Reader(
			$this->notificationStore, $this->queryStore, $this->lb, $this->serializer, $this->forUser, $this->grouping
		);
	}

	/**
	 * @return IWriter
	 */
	public function getWriter() {
		throw new BadMethodCallException();
	}
}
