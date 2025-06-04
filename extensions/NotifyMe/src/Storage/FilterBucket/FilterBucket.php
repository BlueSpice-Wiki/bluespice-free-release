<?php

namespace MediaWiki\Extension\NotifyMe\Storage\FilterBucket;

use MediaWiki\Extension\NotifyMe\Storage\WebNotificationQueryStore;
use MediaWiki\User\UserIdentity;

abstract class FilterBucket implements INotificationFilterBucket {

	/** @var WebNotificationQueryStore */
	protected $store;

	/** @var UserIdentity */
	protected $forUser;

	/** @var string|null */
	protected $forStatus;

	/**
	 * @param WebNotificationQueryStore $store
	 * @param UserIdentity $forUser
	 * @param string|null $forStatus
	 */
	public function __construct( WebNotificationQueryStore $store, UserIdentity $forUser, string $forStatus = 'all' ) {
		$this->store = $store;
		$this->forUser = $forUser;
		$this->forStatus = $forStatus;
	}

	/**
	 * @inheritDoc
	 */
	public function getOptions(): array {
		$res = [];
		$values = $this->query();
		foreach ( $values as $key => $count ) {
			$res[] = $this->makeOption( $key, $count );
		}

		return array_filter( $res );
	}

	/**
	 * @param string $key
	 * @param int $count
	 * @return FilterBucketOption|null
	 */
	abstract protected function makeOption( string $key, int $count ): ?FilterBucketOption;

	/**
	 * @return array
	 */
	abstract protected function query(): array;

}
