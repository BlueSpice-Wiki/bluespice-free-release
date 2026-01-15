<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\GroupStore;

use MWStake\MediaWiki\Component\DataStore\Record;

class GroupRecord extends Record {
	public const GROUP_NAME = 'group_name';
	public const ADDITIONAL_GROUP = 'additional_group';
	public const GROUP_TYPE = 'group_type';
	public const DISPLAY_NAME = 'displayname';
	public const USERCOUNT = 'usercount';
}
