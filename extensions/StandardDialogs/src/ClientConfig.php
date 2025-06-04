<?php

namespace MediaWiki\Extension\StandardDialogs;

use MediaWiki\Config\Config;
use MediaWiki\MediaWikiServices;
use MediaWiki\ResourceLoader\Context as ResourceLoaderContext;

class ClientConfig {

	/**
	 * @param ResourceLoaderContext $context
	 * @param Config $config
	 * @param array $param
	 * @return void
	 */
	public static function makePageInfoPanelModuleRegistryJson(
		ResourceLoaderContext $context,
		Config $config,
		$param
	) {
		$hookContainer = MediaWikiServices::getInstance()->getHookContainer();
		$modules = [];
		$hookContainer->run( 'StandardDialogsRegisterPageInfoPanelModules', [ &$modules, $context, $config ] );
		return $modules;
	}

	/**
	 * @param ResourceLoaderContext $context
	 * @param Config $config
	 * @param array $param
	 * @return void
	 */
	public static function makeDeleteDialogMsgJson(
		ResourceLoaderContext $context,
		Config $config,
		$param
	) {
		$msg = [];
		$msg['confirmdeletetext'] = $context->msg( 'confirmdeletetext' )->parse();
		return $msg;
	}
}
