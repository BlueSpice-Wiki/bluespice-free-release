<?php

namespace MWStake\MediaWiki\Component\InputProcessor\Processor;

use MWStake\MediaWiki\Component\InputProcessor\Processor\Trait\ListSplitterTrait;

class UserListValue extends UserValue {
	use ListSplitterTrait;
}
