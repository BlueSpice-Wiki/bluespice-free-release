<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\ComponentFilter;

use MWStake\MediaWiki\Component\CommonUserInterface\IComponentFilter;

class DefaultFilter implements IComponentFilter {

	/**
	 * @inheritDoc
	 */
	public function shouldRender( $component, $context ): bool {
		return $component->shouldRender( $context );
	}
}
