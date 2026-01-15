<?php

namespace MWStake\MediaWiki\Component\DataStore\Filter;

class TemplateTitle extends Title {
	/**
	 *
	 * @return int
	 */
	protected function getDefaultTitleNamespace() {
		return NS_TEMPLATE;
	}
}
