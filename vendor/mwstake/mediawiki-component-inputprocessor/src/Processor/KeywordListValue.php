<?php

namespace MWStake\MediaWiki\Component\InputProcessor\Processor;

use MWStake\MediaWiki\Component\InputProcessor\Processor\Trait\ListSplitterTrait;

class KeywordListValue extends KeywordValue {
	use ListSplitterTrait;
}
