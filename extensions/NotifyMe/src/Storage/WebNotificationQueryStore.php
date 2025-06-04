<?php

namespace MediaWiki\Extension\NotifyMe\Storage;

use Exception;
use MediaWiki\Extension\NotifyMe\BucketProvider;
use MediaWiki\Extension\NotifyMe\Storage\FilterBucket\CategoryBucket;
use MediaWiki\Extension\NotifyMe\Storage\FilterBucket\INotificationFilterBucket;
use MediaWiki\Extension\NotifyMe\Storage\FilterBucket\NamespaceBucket;
use MediaWiki\Extension\NotifyMe\Storage\FilterBucket\TitleBucket;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Language\Language;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\ITitleEvent;
use MWStake\MediaWiki\Component\Events\Notification;
use Wikimedia\Rdbms\FakeResultWrapper;
use Wikimedia\Rdbms\ILoadBalancer;
use Wikimedia\Rdbms\IResultWrapper;

class WebNotificationQueryStore {
	/** @var ILoadBalancer */
	private $loadBalancer;

	/** @var WikiPageFactory */
	private $wikiPageFactory;

	/** @var TitleFactory */
	private $titleFactory;

	/** @var BucketProvider */
	private $bucketProvider;

	/** @var Language */
	private $language;

	/** @var HookContainer */
	private $hookContainer;

	/**
	 * @param ILoadBalancer $loadBalancer
	 * @param WikiPageFactory $wikiPageFactory
	 * @param TitleFactory $titleFactory
	 * @param Language $language
	 * @param BucketProvider $bucketProvider
	 * @param HookContainer $hookContainer
	 */
	public function __construct(
		ILoadBalancer $loadBalancer, WikiPageFactory $wikiPageFactory, TitleFactory $titleFactory,
		Language $language, BucketProvider $bucketProvider, HookContainer $hookContainer
	) {
		$this->loadBalancer = $loadBalancer;
		$this->wikiPageFactory = $wikiPageFactory;
		$this->titleFactory = $titleFactory;
		$this->language = $language;
		$this->bucketProvider = $bucketProvider;
		$this->hookContainer = $hookContainer;
	}

	/**
	 * @param Notification $notification
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function add( Notification $notification ): bool {
		$db = $this->loadBalancer->getConnection( DB_PRIMARY );
		$data = [
			'nwqs_notification_id' => $notification->getId(),
			'nwqs_target_user' => $notification->getTargetUser()->getId(),
			'nwqs_notification_timestamp' => $db->timestamp( $notification->getEvent()->getTime() ),
			'nwqs_status' => $notification->getStatus()->getStatus(),
			'nwqs_buckets' => $this->getBuckets( $notification ),
		];
		if ( $notification->getEvent() instanceof ITitleEvent ) {
			$title = $notification->getEvent()->getTitle();
			$data['nwqs_namespace_id'] = $title->getNamespace();
			$data['nwqs_namespace_text'] = $title->getNsText();
			$data['nwqs_title'] = $title->getDBkey();
			$data['nwqs_categories'] = $this->getCategories( $title );
		}

		try {
			return $db->insert(
				'notifications_web_query_store',
				$data,
				__METHOD__
			);
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Get available filter values for a user
	 * @param UserIdentity $user
	 * @param string $status "pending"/"completed"/"all", depending on which notifications we need information about
	 *
	 * @return array
	 */
	public function getFilterMeta( UserIdentity $user, string $status ) {
		$buckets = [
			new TitleBucket( $this->getTotalCount( $status, $user ) ),
			new CategoryBucket( $this->titleFactory, $this, $user, $status ),
			new NamespaceBucket( $this->language, $this, $user, $status ),
		];

		$this->hookContainer->run( 'NotifyMeGetFilterMeta', [ &$buckets, $this, $user, $status ] );

		return $this->serializeBuckets( $buckets );
	}

	/**
	 * @param INotificationFilterBucket[] $buckets
	 * @return array
	 */
	private function serializeBuckets( array $buckets ): array {
		$res = [];
		foreach ( $buckets as $bucket ) {
			$res[] = [
				'label' => $bucket->getLabel()->text(),
				'type' => $bucket->getType(),
				'items' => array_map(
					static function ( $option ) {
						return [
							'key' => $option->getDataKey(),
							'count' => $option->getCount(),
							'label' => $option->getLabel()->text(),
						];
					},
					$bucket->getOptions()
				)
			];
		}

		return $res;
	}

	/**
	 * Truncate table
	 *
	 * @return void
	 * @throws Exception
	 */
	public function clearAll() {
		if ( !defined( 'DO_MAINTENANCE' ) ) {
			throw new Exception( 'This method can only be called from maintenance scripts' );
		}
		$db = $this->loadBalancer->getConnection( DB_PRIMARY );
		$db->query( 'TRUNCATE TABLE notifications_web_query_store', __METHOD__ );
	}

	/**
	 * @param array|null $conds
	 * @param array|null $options
	 *
	 * @return \Wikimedia\Rdbms\IResultWrapper
	 */
	public function query( ?array $conds = [], ?array $options = [] ) {
		$eventTypes = $this->bucketProvider->getEventTypes();
		if ( empty( $eventTypes ) ) {
			return new FakeResultWrapper( [] );
		}
		$db = $this->loadBalancer->getConnection( DB_REPLICA );
		$conds[] = 'ni_event_type IN (' . $db->makeList( $eventTypes ) . ')';

		return $db->select(
			[ 'notifications_web_query_store', 'notifications_instance' ],
			[
				'ni_event_type',
				'nwqs_notification_id',
				'nwqs_target_user',
				'nwqs_notification_timestamp',
				'nwqs_status',
				'nwqs_namespace_id',
				'nwqs_namespace_text',
				'nwqs_title',
				'nwqs_categories',
				'nwqs_buckets'
			],
			$conds,
			__METHOD__,
			$options,
			[ 'notifications_instance' => [ 'INNER JOIN', 'ni_id = nwqs_notification_id' ] ]
		);
	}

	/**
	 * @param UserIdentity $forUser
	 * @param string $forStatus
	 * @param array $fields
	 * @param array $conds
	 * @param array $options
	 * @return IResultWrapper
	 */
	public function rawQuery(
		UserIdentity $forUser, string $forStatus, array $fields, array $conds = [], array $options = []
	): IResultWrapper {
		$eventTypes = $this->bucketProvider->getEventTypes();
		if ( empty( $eventTypes ) ) {
			return new FakeResultWrapper( [] );
		}
		$db = $this->loadBalancer->getConnection( DB_REPLICA );
		$conds[] = 'ni_event_type IN (' . $db->makeList( $eventTypes ) . ')';
		$conds['nwqs_target_user'] = $forUser->getId();

		if ( $forStatus !== 'all' ) {
			$conds['nwqs_status'] = $forStatus;
		}

		return $db->select(
			[ 'notifications_web_query_store', 'notifications_instance' ],
			$fields,
			$conds,
			__METHOD__,
			$options,
			[ 'notifications_instance' => [ 'INNER JOIN', 'ni_id = nwqs_notification_id' ] ]
		);
	}

	/**
	 * Same as {@link WebNotificationQueryStore::count()}, but just counts read/unread/all notifications.
	 *
	 * @param string $status "pending"/"completed"/"all", depending on which notifications we need information about
	 * @param UserIdentity $user
	 * @return int
	 */
	public function getTotalCount( string $status, UserIdentity $user ): int {
		$res = $this->rawQuery( $user, $status, [ 'COUNT( nwqs_notification_id ) as count' ] );
		if ( !$res->numRows() ) {
			return 0;
		}
		$row = $res->fetchObject();
		return (int)$row->count;
	}

	/**
	 * @param Notification $notification
	 *
	 * @return bool
	 */
	public function update( Notification $notification ): bool {
		$db = $this->loadBalancer->getConnection( DB_PRIMARY );
		try {
			return $db->update(
				'notifications_web_query_store',
				[
					'nwqs_status' => $notification->getStatus()->getStatus(),
				],
				[
					'nwqs_notification_id' => $notification->getId()
				],
				__METHOD__
			);
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * @param Title $title
	 *
	 * @return string
	 */
	private function getCategories( Title $title ): string {
		if ( !$title->exists() ) {
			return '';
		}
		$wikiPage = $this->wikiPageFactory->newFromTitle( $title );
		$po = $wikiPage->getParserOutput();
		if ( !$po ) {
			return '';
		}
		$categories = $po->getCategoryNames();

		return implode( '|', $categories );
	}

	/**
	 * @param Notification $notification
	 *
	 * @return string
	 * @throws Exception
	 */
	private function getBuckets( Notification $notification ): string {
		$buckets = $this->bucketProvider->getEventBuckets( $notification->getEvent() );
		return implode( '|', $buckets );
	}
}
