<?php

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

namespace MediaWiki\Extension\EnhancedStandardUIs\HookHandler;

use MediaWiki\Config\Config;
use MediaWiki\Extension\EnhancedStandardUIs\Special\EnhancedAllPages;
use MediaWiki\Extension\EnhancedStandardUIs\Special\EnhancedFilelist;
use MediaWiki\Extension\EnhancedStandardUIs\Special\EnhancedPreferences;
use MediaWiki\Extension\EnhancedStandardUIs\Special\EnhancedSpecialPages;
use MediaWiki\SpecialPage\Hook\SpecialPage_initListHook;

class OverwriteSpecialPages implements SpecialPage_initListHook {

	/** @var Config */
	private $config;

	/**
	 *
	 * @param Config $config
	 */
	public function __construct( Config $config ) {
		$this->config = $config;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function onSpecialPage_initList( &$list ) {
		if ( $this->config->get( 'EnhancedUIsAllPagesOverride' ) ) {
			$list['Allpages'] = [ 'class' => EnhancedAllPages::class ];
		}

		if ( $this->config->get( 'EnhancedUIsFilelistOverride' ) ) {
			$list['Listfiles'] = [ 'class' => EnhancedFilelist::class ];
		}

		if ( $this->config->get( 'EnhancedUIsSpecialSpecialPagesOverride' ) ) {
			$list['Specialpages'] = [ 'class' => EnhancedSpecialPages::class ];
		}

		if ( $this->config->get( 'EnhancedUIsSpecialPreferencesOverride' ) ) {
			$list['Preferences'] = [
				'class' => EnhancedPreferences::class,
				'services' => [ "PreferencesFactory", "MainConfig", "MessageFormatterFactory" ]
			];
		}

		return true;
	}
}
