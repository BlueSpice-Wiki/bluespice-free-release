<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\FileQueryStore;

use MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleQueryStore\TitleRecord;

class FileRecord extends TitleRecord {
	public const FILE_EXTENSION = 'file_extension';
	public const MIME_MAJOR = 'img_mime_major';
	public const MIME_MINOR = 'img_mime_minor';
	public const FILE_SIZE = 'file_size';
	public const FILE_MEDIATYPE = 'media_type';
	public const FILE_AUTHOR_NAME = 'author';
	public const FILE_TIMESTAMP = 'timestamp';
	public const FILE_TIMESTAMP_FORMATTED = 'formatted_ts';
	public const FILE_COMMENT = 'comment';
	public const FILE_CATEGORIES = 'categories';
	public const FILE_THUMBNAIL_URL = 'thumb_url';
	public const FILE_THUMBNAIL_URL_PREVIEW = 'preview_url';
	public const FILE_AUTHOR_ID = 'author_id';
	public const FILE_WIDTH = 'width';
	public const FILE_HEIGHT = 'height';
}
