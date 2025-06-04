<?php

namespace MediaWiki\Extension\MenuEditor;

use MediaWiki\Config\Config;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\ResourceLoader\Context as ResourceLoaderContext;

class ClientConfig {

	/**
	 * @param ResourceLoaderContext $context
	 * @param Config $config
	 * @return array
	 */
	public static function makeConfigJson(
		ResourceLoaderContext $context,
		Config $config
	) {
		$allowedMediawikiSidebarKeywords = $config->get( 'MenuEditorMediawikiSidebarAllowedKeywords' );
		return [
			'allowedMediawikiSidebarKeywords' => $allowedMediawikiSidebarKeywords
		];
	}

	/**
	 * @param ResourceLoaderContext $context
	 * @param Config $config
	 * @return array
	 */
	public static function getToolbarTools(
		ResourceLoaderContext $context,
		Config $config
	) {
		$registeredTools = ExtensionRegistry::getInstance()->getAttribute(
			'MenuEditorToolbarTools'
		);
		$tools = [];
		$modules = [];
		foreach ( $registeredTools as $tool => $toolConfig ) {
			if ( !isset( $toolConfig[ 'module' ] ) ) {
				continue;
			}
			if ( !isset( $toolConfig[ 'classname' ] ) ) {
				continue;
			}
			$classname = $toolConfig['classname'];
			$groupname = $toolConfig['group']['name'] ?? '';
			$prio = $toolConfig['group']['priority'] ?? 3;
			$classes = $toolConfig['group']['classes'] ?? [];
			$tools[ $tool ] = [
				'classname' => $classname,
				'group' => [
					'name' => $groupname,
					'priority' => $prio,
					'classes' => $classes
				]
			];
			if ( !in_array( $toolConfig[ 'module' ], $modules ) ) {
				$modules[ $tool ] = $toolConfig[ 'module' ];
			}
		}
		return [
			'tools' => $tools,
			'modules' => $modules
		];
	}
}
