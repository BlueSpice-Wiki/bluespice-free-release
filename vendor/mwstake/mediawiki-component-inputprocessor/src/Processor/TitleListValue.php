<?php

namespace MWStake\MediaWiki\Component\InputProcessor\Processor;

use MWStake\MediaWiki\Component\InputProcessor\Processor\Trait\ListSplitterTrait;

class TitleListValue extends TitleValue {
	use ListSplitterTrait;
}
