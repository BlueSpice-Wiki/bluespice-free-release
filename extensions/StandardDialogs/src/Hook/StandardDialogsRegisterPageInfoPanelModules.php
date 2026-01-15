<?php

namespace MediaWiki\Extension\StandardDialogs\Hook;

use MediaWiki\Config\Config;
use MediaWiki\ResourceLoader\Context as ResourceLoaderContext;

interface StandardDialogsRegisterPageInfoPanelModules {

	/**
	 * @param string[] &$modules
	 * @param ResourceLoaderContext $context
	 * @param Config $config
	 * @return void
	 */
	public function onStandardDialogsRegisterPageInfoPanelModules(
		&$modules, ResourceLoaderContext $context, Config $config
	): void;

}
