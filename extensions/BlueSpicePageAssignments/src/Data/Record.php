<?php

namespace BlueSpice\PageAssignments\Data;

class Record extends \MWStake\MediaWiki\Component\DataStore\Record {
	public const PAGE_ID = 'pa_page_id';
	public const ASSIGNEE_KEY = 'pa_assignee_key';
	public const ASSIGNEE_TYPE = 'pa_assignee_type';
	public const POSITION = 'pa_position';
	public const ANCHOR = 'anchor';
	public const ID = 'id';
	public const TEXT = 'text';
}
