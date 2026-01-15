<?php

namespace BlueSpice\WhoIsOnline\Data;

class Record extends \MWStake\MediaWiki\Component\DataStore\Record {
	public const ID = 'wo_id';
	public const USER_ID = 'wo_user_id';
	public const USER_NAME = 'wo_user_name';
	public const USER_REAL_NAME = 'wo_user_real_name';
	public const PAGE_ID = 'wo_page_id';
	public const PAGE_NAMESPACE = 'wo_page_namespace';
	public const PAGE_TITLE = 'wo_page_title';
	public const TIMESTAMP = 'wo_log_ts';
	public const ACTION = 'wo_action';
}
