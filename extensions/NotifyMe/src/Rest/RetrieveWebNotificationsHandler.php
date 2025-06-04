<?php

namespace MediaWiki\Extension\NotifyMe\Rest;

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\NotifyMe\MediaWiki\Data\WebNotifications\Store;
use MediaWiki\Extension\NotifyMe\NotificationSerializer;
use MediaWiki\Extension\NotifyMe\Storage\NotificationStore;
use MediaWiki\Extension\NotifyMe\Storage\WebNotificationQueryStore;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Rest\Response;
use MWStake\MediaWiki\Component\CommonWebAPIs\Rest\QueryStore;
use MWStake\MediaWiki\Component\DataStore\IStore;
use MWStake\MediaWiki\Component\DataStore\ResultSet;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\Rdbms\ILoadBalancer;

class RetrieveWebNotificationsHandler extends QueryStore {

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
	 * @param HookContainer $hookContainer
	 * @param NotificationStore $notificationStore
	 * @param WebNotificationQueryStore $queryStore
	 * @param ILoadBalancer $lb
	 * @param NotificationSerializer $serializer
	 */
	public function __construct(
		HookContainer $hookContainer, NotificationStore $notificationStore,
		WebNotificationQueryStore $queryStore, ILoadBalancer $lb, NotificationSerializer $serializer
	) {
		parent::__construct( $hookContainer );
		$this->notificationStore = $notificationStore;
		$this->queryStore = $queryStore;
		$this->lb = $lb;
		$this->serializer = $serializer;
	}

	/**
	 * @return \MediaWiki\Rest\Response
	 */
	public function execute() {
		$validated = $this->getValidatedParams();
		if ( $validated['meta'] ) {
			return $this->getResponseFactory()->createJson(
				$this->queryStore->getFilterMeta( RequestContext::getMain()->getUser(), $validated['status'] )
			);
		}

		return parent::execute();
	}

	/**
	 * @param ResultSet $result
	 * @return Response
	 */
	protected function returnResult( ResultSet $result ): Response {
		return $this->getResponseFactory()->createJson( [
			'results' => $result->getRecords(),
			'total' => $result->getTotal(),
			'processed' => $result->getProcessedCount(),
			'items_total' => $result->getItemsCount()
		] );
	}

	/**
	 * @return IStore
	 */
	protected function getStore(): IStore {
		$validated = $this->getValidatedParams();

		return new Store(
			$this->notificationStore, $this->queryStore,
			$this->lb, $this->serializer, RequestContext::getMain()->getUser(), $validated['group']
		);
	}

	/**
	 * @return array|array[]
	 */
	public function getParamSettings() {
		return parent::getParamSettings() + [
			'meta' => [
				static::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_TYPE => 'boolean',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_DEFAULT => false,
			],
			// Parameter below is used only in combination with "meta" => true
			// To get meta information for filter about read/unread notifications only
			'status' => [
				static::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_DEFAULT => 'all',
			],
			'group' => [
				static::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_TYPE => 'boolean',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_DEFAULT => true,
			]
		];
	}
}
