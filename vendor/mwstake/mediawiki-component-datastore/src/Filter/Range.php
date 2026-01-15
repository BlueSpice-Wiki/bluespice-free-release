<?php

namespace MWStake\MediaWiki\Component\DataStore\Filter;

use MWStake\MediaWiki\Component\DataStore\Filter;

abstract class Range extends Filter {
	public const COMPARISON_LOWER_THAN = 'lt';
	public const COMPARISON_GREATER_THAN = 'gt';
}
