<?php

namespace MediaWiki\Extension\NotifyMe\Storage\FilterBucket;

use MediaWiki\Extension\NotifyMe\Storage\WebNotificationQueryStore;
use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\UserIdentity;

class CategoryBucket extends FilterBucket {

	/** @var TitleFactory */
	private $titleFactory;

	/**
	 * @param TitleFactory $titleFactory
	 * @param WebNotificationQueryStore $store
	 * @param UserIdentity $forUser
	 * @param string $forStatus
	 */
	public function __construct(
		TitleFactory $titleFactory, WebNotificationQueryStore $store, UserIdentity $forUser, string $forStatus = 'all'
	) {
		parent::__construct( $store, $forUser, $forStatus );
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function getLabel(): Message {
		return Message::newFromKey( 'notifyme-notification-center-filter-label-categories' );
	}

	/**
	 * @return string
	 */
	public function getType(): string {
		return 'category';
	}

	/**
	 * @param string $key
	 * @param int $count
	 * @return FilterBucketOption|null
	 */
	protected function makeOption( string $key, int $count ): ?FilterBucketOption {
		if ( $key === 'none' ) {
			return new FilterBucketOption(
				Message::newFromKey( 'notifyme-notification-center-filter-label-categories-none' ),
				'',
				$count
			);
		}
		$categoryTitle = $this->titleFactory->makeTitle( NS_CATEGORY, $key );
		return new FilterBucketOption(
			new RawMessage( $categoryTitle->getText() ),
			$key,
			$count
		);
	}

	/**
	 * @return array
	 */
	protected function query(): array {
		$res = $this->store->rawQuery( $this->forUser, $this->forStatus, [ 'nwqs_categories' ] );

		$counts = [ 'none' => 0 ];
		foreach ( $res as $row ) {
			if ( !$row->nwqs_categories ) {
				$counts['none']++;
				continue;
			}
			$parts = explode( '|', $row->nwqs_categories );
			foreach ( $parts as $part ) {
				if ( !isset( $counts[$part] ) ) {
					$counts[$part] = 0;
				}
				$counts[$part]++;
			}
		}

		return $counts;
	}
}
