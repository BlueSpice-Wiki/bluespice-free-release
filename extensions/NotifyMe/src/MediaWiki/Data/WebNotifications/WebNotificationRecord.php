<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\Data\WebNotifications;

use MWStake\MediaWiki\Component\DataStore\Record;

class WebNotificationRecord extends Record {
	public const ID = 'id';
	public const ENTITY_TYPE = 'entity_type';
	public const MESSAGE = 'message';
	public const LINKS = 'links';
	public const AGENT = 'agent';
	public const AGENT_IS_BOT = 'agent_is_bot';
	public const TIMESTAMP = 'timestamp';
	public const USER_TIMESTAMP = 'user_timestamp';
	public const STATUS = 'status';
	public const SOURCE_PROVIDERS = 'source_providers';

	public const COUNT = 'count';
	public const NOTIFICATIONS = 'notifications';

	public const NAMESPACE_ID = 'namespace_id';
	public const NAMESPACE_TEXT = 'namespace_text';
	public const BUCKETS = 'buckets';
	public const CATEGORIES = 'categories';
}
