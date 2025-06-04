<?php

namespace BlueSpice\Readers\Data;

class Record extends \MWStake\MediaWiki\Component\DataStore\Record {
	public const ID = 'readers_id';
	public const USER_ID = 'readers_user_id';
	public const USER_NAME = 'readers_user_name';
	public const USER_REAL_NAME = 'readers_user_real_name';
	public const PAGE_ID = 'readers_page_id';
	public const REV_ID = 'readers_rev_id';
	public const TIMESTAMP = 'readers_ts';
	public const USER_IMAGE_HTML = 'user_image_html';
	public const DATETIME = 'datetime';
	public const REV_DATETIME = 'rev_datetime';
}
