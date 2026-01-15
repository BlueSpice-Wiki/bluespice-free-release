<?php

namespace MediaWiki\Extension\NotifyMe\Storage\FilterBucket;

use MediaWiki\Extension\NotifyMe\Storage\WebNotificationQueryStore;
use MediaWiki\Language\Language;
use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;
use MediaWiki\User\UserIdentity;

class NamespaceBucket extends FilterBucket {

	/** @var Language */
	private $language;

	/**
	 * @param Language $language
	 * @param WebNotificationQueryStore $store
	 * @param UserIdentity $forUser
	 * @param string $forStatus
	 */
	public function __construct(
		Language $language, WebNotificationQueryStore $store, UserIdentity $forUser, string $forStatus = 'all'
	) {
		parent::__construct( $store, $forUser, $forStatus );
		$this->language = $language;
	}

	/**
	 * @inheritDoc
	 */
	public function getLabel(): Message {
		return Message::newFromKey( 'notifyme-notification-center-filter-label-namespaces' );
	}

	/**
	 * @return string
	 */
	public function getType(): string {
		return 'namespace';
	}

	/**
	 * @param string $key
	 * @param int $count
	 * @return FilterBucketOption|null
	 */
	protected function makeOption( string $key, int $count ): ?FilterBucketOption {
		$nsText = $this->language->getNsText( $key );
		if ( $nsText === false ) {
			return null;
		}

		return new FilterBucketOption(
			new RawMessage( $nsText ),
			$key,
			$count
		);
	}

	/**
	 * @return array
	 */
	protected function query(): array {
		$res = $this->store->rawQuery(
			$this->forUser, $this->forStatus, [ 'nwqs_namespace_id', 'COUNT( nwqs_namespace_id ) as count' ],
			[], [ 'GROUP BY' => 'nwqs_namespace_id' ]
		);

		$counts = [];
		foreach ( $res as $row ) {
			if ( !$row->nwqs_namespace_id || (int)$row->count === 0 ) {
				continue;
			}

			$counts[(int)$row->nwqs_namespace_id] = (int)$row->count;
		}

		return $counts;
	}
}
