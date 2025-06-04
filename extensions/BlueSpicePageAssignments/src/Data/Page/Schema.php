<?php

namespace BlueSpice\PageAssignments\Data\Page;

use BlueSpice\Data\Page\Schema as PageSchema;
use MWStake\MediaWiki\Component\DataStore\FieldType;

class Schema extends \MWStake\MediaWiki\Component\DataStore\Schema {
	public const TABLE_NAME = PageSchema::TABLE_NAME;

	public function __construct() {
		parent::__construct( array_merge( (array)( new PageSchema ), [
			Record::ASSIGNMENTS => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
			Record::PREFIXED_TEXT => [
				self::FILTERABLE => true,
				self::SORTABLE => true,
				self::TYPE => FieldType::STRING
			],
		] ) );
	}
}
