<?php

namespace MWStake\MediaWiki\Component\InputProcessor\Processor;

use MWStake\MediaWiki\Component\InputProcessor\Processor\Trait\ListSplitterTrait;

class NamespaceListValue extends NamespaceValue {
	use ListSplitterTrait;
}
