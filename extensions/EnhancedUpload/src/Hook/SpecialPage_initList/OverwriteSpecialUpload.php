<?php

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

namespace MediaWiki\Extension\EnhancedUpload\Hook\SpecialPage_initList;

use MediaWiki\Extension\EnhancedUpload\Special\EnhancedUpload;
use MediaWiki\MediaWikiServices;

class OverwriteSpecialUpload {

	/**
	 *
	 * @param array &$specialPages
	 * @return void
	 */
	public static function onSpecialPage_initList( &$specialPages ) {
		$config = MediaWikiServices::getInstance()->getMainConfig();

		if ( $config->get( 'EnhancedUploadOverrideStandardUpload' ) ) {
			$specialPages['Upload'] = EnhancedUpload::class;
		}
		return true;
	}
}
