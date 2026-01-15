<?php

namespace MediaWiki\Extension\NotifyMe\Storage\FilterBucket;

use MediaWiki\Message\Message;

class TitleBucket implements INotificationFilterBucket {

	/** @var int */
	private $totalCount;

	/**
	 * @param int $totalCount
	 */
	public function __construct( int $totalCount ) {
		$this->totalCount = $totalCount;
	}

	/**
	 * @inheritDoc
	 */
	public function getLabel(): Message {
		return Message::newFromKey( 'notifyme-notification-center-filter-title' );
	}

	/**
	 * @return string
	 */
	public function getType(): string {
		return 'title';
	}

	/**
	 * @inheritDoc
	 */
	public function getOptions(): array {
		return [
			new FilterBucketOption(
				Message::newFromKey( 'notifyme-notification-center-filter-label-all' ),
				'all',
				$this->totalCount
			),
		];
	}
}
