<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleTreeStore;

use MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleQueryStore\TitleRecord;

class TitleTreeRecord extends TitleRecord {
	public const ID = 'id';
	public const EXPANDED = 'expanded';
	public const LOADED = 'loaded';
	public const LEAF = 'leaf';
	public const CHILDREN = 'children';
	public const ALLOWS_SUBPAGES = 'allows_subpages';
}
