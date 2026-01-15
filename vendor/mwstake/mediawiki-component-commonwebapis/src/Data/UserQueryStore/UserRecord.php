<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\UserQueryStore;

use MWStake\MediaWiki\Component\DataStore\Record;

class UserRecord extends Record {
	public const ID = 'user_id';
	public const USER_NAME = 'user_name';
	public const USER_REAL_NAME = 'user_real_name';
	public const USER_REGISTRATION = 'user_registration';
	public const USER_EDITCOUNT = 'user_editcount';
	public const USER_EMAIL = 'user_email';
	public const GROUPS = 'groups';
	public const GROUPS_RAW = 'groups_raw';
	public const ENABLED = 'enabled';
	public const DISPLAY_NAME = 'display_name';
	public const DISPLAY_HTML = 'display_html';
	public const PAGE_LINK = 'page_link';
	public const PAGE_URL = 'page_url';
	public const PAGE_PREFIXED_TEXT = 'page_prefixed_text';
	public const USER_IMAGE = 'user_image';
}
