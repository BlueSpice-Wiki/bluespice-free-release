<?php

namespace MediaWiki\Extension\NotifyMe\Storage\FilterBucket;

use MediaWiki\Message\Message;

interface INotificationFilterBucket {

	/**
	 * @return Message
	 */
	public function getLabel(): Message;

	/**
	 * Filter type, to be passed to the store
	 *
	 * @return string
	 */
	public function getType(): string;

	/**
	 * @return FilterBucketOption[]
	 */
	public function getOptions(): array;
}
