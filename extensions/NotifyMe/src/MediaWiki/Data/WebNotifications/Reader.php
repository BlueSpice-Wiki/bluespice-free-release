<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\Data\WebNotifications;

use MediaWiki\Extension\NotifyMe\NotificationSerializer;
use MediaWiki\Extension\NotifyMe\Storage\NotificationStore;
use MediaWiki\Extension\NotifyMe\Storage\WebNotificationQueryStore;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\DataStore\LimitOffsetTrimmer;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use MWStake\MediaWiki\Component\DataStore\ResultSet;
use Wikimedia\Rdbms\ILoadBalancer;

class Reader extends \MWStake\MediaWiki\Component\DataStore\Reader {

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
		parent::__construct();
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
	 * @param ReaderParams $params
	 * @return ResultSet
	 */
	public function read( $params ) {
		$db = $this->lb->getConnection( DB_REPLICA );
		$primaryDataProvider = new PrimaryDataProvider( $this->queryStore, $db, $this->getSchema(), $this->forUser );
		$dataSets = $primaryDataProvider->makeData( $params );
		$total = count( $dataSets );

		$sorter = $this->makeSorter( $params );
		$dataSets = $sorter->sort(
			$dataSets,
			$this->getSchema()->getUnsortableFields()
		);

		$trimmer = $this->makeTrimmer( $params );
		$dataSets = $trimmer->trim( $dataSets );
		$secondaryDataProvider = new SecondaryDataProvider(
			$this->notificationStore, $this->serializer, $this->forUser, $this->grouping, $params->getLimit()
		);
		[ $dataSets, $itemsCount, $processedCount ] = $secondaryDataProvider->extend( $dataSets );

		return new NotificationResultSet( $dataSets, $total, $itemsCount, $processedCount );
	}

	/**
	 * @param ReaderParams $params
	 * @return LimitOffsetTrimmer
	 */
	protected function makeTrimmer( $params ) {
		// Only trim for the offset, limit will be trimmed in SecondaryDataProvider
		return new LimitOffsetTrimmer(
			ReaderParams::LIMIT_INFINITE,
			$params->getStart()
		);
	}

	/**
	 * @inheritDoc
	 */
	protected function makePrimaryDataProvider( $params ) {
		return null;
	}

	/**
	 * @return null
	 */
	protected function makeSecondaryDataProvider() {
		return null;
	}
}
