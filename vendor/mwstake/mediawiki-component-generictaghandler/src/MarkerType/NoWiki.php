<?php

namespace MWStake\MediaWiki\Component\GenericTagHandler\MarkerType;

use MWStake\MediaWiki\Component\GenericTagHandler\MarkerType;

class NoWiki extends MarkerType {

	/**
	 * @return string
	 */
	protected function getName(): string {
		return 'nowiki';
	}
}
