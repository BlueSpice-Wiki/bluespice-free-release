<?php

namespace BlueSpice\TagCloud\Data\TagCloud;

class Record extends \MWStake\MediaWiki\Component\DataStore\Record {
	public const NAME = 'name';
	public const COUNT = 'count';
	public const LINK = 'link';
	public const RENDEREDLINK = 'renderedlink';
}
