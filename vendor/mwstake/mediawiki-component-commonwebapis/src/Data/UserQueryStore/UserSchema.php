<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\UserQueryStore;

use MWStake\MediaWiki\Component\DataStore\FieldType;
use MWStake\MediaWiki\Component\DataStore\Schema;

class UserSchema extends Schema {
	public function __construct() {
		parent::__construct( [
			UserRecord::ID => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::STRING
			],
			UserRecord::USER_NAME => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			UserRecord::USER_REAL_NAME => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			UserRecord::USER_REGISTRATION => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			UserRecord::USER_EMAIL => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			UserRecord::USER_EDITCOUNT => [
				self::FILTERABLE => false,
				self::SORTABLE => true,
				self::TYPE => FieldType::INT
			],
			UserRecord::GROUPS => [
				self::FILTERABLE => false,
				self::SORTABLE => false ,
				self::TYPE => FieldType::LISTVALUE
			],
			UserRecord::GROUPS_RAW => [
				self::FILTERABLE => true,
				self::SORTABLE => false ,
				self::TYPE => FieldType::LISTVALUE
			],
			UserRecord::ENABLED => [
				self::FILTERABLE => true,
				self::SORTABLE => true ,
				self::TYPE => FieldType::BOOLEAN
			],
			UserRecord::DISPLAY_NAME => [
				self::FILTERABLE => false,
				self::SORTABLE => false ,
				self::TYPE => FieldType::STRING
			],
			UserRecord::PAGE_LINK => [
				self::FILTERABLE => false,
				self::SORTABLE => false ,
				self::TYPE => FieldType::STRING
			],
			UserRecord::PAGE_PREFIXED_TEXT => [
				self::FILTERABLE => false,
				self::SORTABLE => false ,
				self::TYPE => FieldType::STRING
			],
			UserRecord::USER_IMAGE => [
				self::FILTERABLE => false,
				self::SORTABLE => false ,
				self::TYPE => FieldType::STRING
			]
		] );
	}
}
