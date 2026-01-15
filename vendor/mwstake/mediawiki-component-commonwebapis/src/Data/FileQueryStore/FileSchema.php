<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\FileQueryStore;

use MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleQueryStore\TitleSchema;
use MWStake\MediaWiki\Component\DataStore\FieldType;

class FileSchema extends TitleSchema {
	public function __construct() {
		parent::__construct( [
			FileRecord::FILE_EXTENSION => [
				self::FILTERABLE => true,
				self::SORTABLE => true ,
				self::TYPE => FieldType::STRING
			],
			FileRecord::MIME_MAJOR => [
				self::FILTERABLE => true,
				self::SORTABLE => true ,
				self::TYPE => FieldType::STRING
			],
			FileRecord::MIME_MINOR => [
				self::FILTERABLE => true,
				self::SORTABLE => true ,
				self::TYPE => FieldType::STRING
			],
			FileRecord::FILE_SIZE => [
				self::FILTERABLE => true,
				self::SORTABLE => true ,
				self::TYPE => FieldType::INT
			],
			FileRecord::FILE_AUTHOR_NAME => [
				self::FILTERABLE => true,
				self::SORTABLE => true ,
				self::TYPE => FieldType::STRING
			],
			FileRecord::FILE_TIMESTAMP => [
				self::FILTERABLE => true,
				self::SORTABLE => true ,
				self::TYPE => FieldType::STRING
			],
			FileRecord::FILE_TIMESTAMP_FORMATTED => [
				self::FILTERABLE => true,
				self::SORTABLE => true ,
				self::TYPE => FieldType::STRING
			],
			FileRecord::FILE_COMMENT => [
				self::FILTERABLE => true,
				self::SORTABLE => true ,
				self::TYPE => FieldType::STRING
			],
			FileRecord::FILE_CATEGORIES => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::LISTVALUE
			],
			FileRecord::FILE_THUMBNAIL_URL => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::STRING
			],
			FileRecord::FILE_THUMBNAIL_URL_PREVIEW => [
				self::FILTERABLE => false,
				self::SORTABLE => false,
				self::TYPE => FieldType::STRING
			],
			FileRecord::FILE_MEDIATYPE => [
				self::FILTERABLE => false,
				self::SORTABLE => false ,
				self::TYPE => FieldType::STRING
			],
			FileRecord::FILE_HEIGHT => [
				self::FILTERABLE => false,
				self::SORTABLE => false ,
				self::TYPE => FieldType::INT
			],
			FileRecord::FILE_WIDTH => [
				self::FILTERABLE => false,
				self::SORTABLE => false ,
				self::TYPE => FieldType::INT
			],
		] );
	}
}
