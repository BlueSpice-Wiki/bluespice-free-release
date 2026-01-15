<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\Data\WebNotifications;

use MWStake\MediaWiki\Component\DataStore\FieldType;
use MWStake\MediaWiki\Component\DataStore\Schema;

class WebNotificationSchema extends Schema {
	public function __construct() {
		parent::__construct( [
			WebNotificationRecord::ID => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::INT
			],
			WebNotificationRecord::ENTITY_TYPE => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			WebNotificationRecord::MESSAGE => [
				self::FILTERABLE => true,
				self::SORTABLE => false,
				self::TYPE => FieldType::STRING
			],
			WebNotificationRecord::LINKS => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::STRING
			],
			WebNotificationRecord::AGENT => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			WebNotificationRecord::AGENT_IS_BOT => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::BOOLEAN
			],
			WebNotificationRecord::TIMESTAMP => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			WebNotificationRecord::USER_TIMESTAMP => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::STRING
			],
			WebNotificationRecord::STATUS => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			WebNotificationRecord::SOURCE_PROVIDERS => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::STRING
			],
			WebNotificationRecord::COUNT => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::INT
			],
			WebNotificationRecord::NOTIFICATIONS => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::STRING
			],
			WebNotificationRecord::NAMESPACE_ID => [
				self::FILTERABLE => true,
				self::SORTABLE => false,
				self::TYPE => FieldType::INT
			],
			WebNotificationRecord::NAMESPACE_TEXT => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			WebNotificationRecord::CATEGORIES => [
				self::FILTERABLE => true,
				self::SORTABLE => false,
				self::TYPE => FieldType::LISTVALUE
			],
			WebNotificationRecord::BUCKETS => [
				self::FILTERABLE => true,
				self::SORTABLE => false,
				self::TYPE => FieldType::LISTVALUE
			],
		] );
	}
}
