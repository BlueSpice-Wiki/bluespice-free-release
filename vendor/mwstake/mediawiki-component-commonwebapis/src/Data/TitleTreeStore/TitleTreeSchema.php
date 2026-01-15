<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleTreeStore;

use MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleQueryStore\TitleSchema;
use MWStake\MediaWiki\Component\DataStore\FieldType;

class TitleTreeSchema extends TitleSchema {
	public function __construct() {
		parent::__construct( [
			TitleTreeRecord::ID => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::STRING
			],
			TitleTreeRecord::LEAF => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::BOOLEAN
			],
			TitleTreeRecord::EXPANDED => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::BOOLEAN
			],
			TitleTreeRecord::LOADED => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::BOOLEAN
			],
			TitleTreeRecord::CHILDREN => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::LISTVALUE
			],
			TitleTreeRecord::ALLOWS_SUBPAGES => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::BOOLEAN
			]
		] );
	}
}
