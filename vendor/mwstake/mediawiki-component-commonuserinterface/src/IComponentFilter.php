<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use MediaWiki\Context\IContextSource;

interface IComponentFilter {

	/**
	 *
	 * @param IComponent $component
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IComponent $component, IContextSource $context ): bool;

}
