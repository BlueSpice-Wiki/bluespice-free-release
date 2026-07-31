<?php

namespace MediaWiki\Extension\EnhancedUpload\Hook\SpecialPage_initList; // phpcs:ignore MediaWiki.NamingConventions.NamespaceName.NamespaceUnderscore, Generic.Files.LineLength.TooLong

use MediaWiki\Extension\EnhancedUpload\Special\EnhancedUpload;
use MediaWiki\MediaWikiServices;

class OverwriteSpecialUpload {

	/**
	 * @param array &$specialPages
	 * @return void
	 */
	public static function onSpecialPage_initList( &$specialPages ) { // phpcs:ignore MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName, Generic.Files.LineLength.TooLong
		$config = MediaWikiServices::getInstance()->getMainConfig();

		if ( $config->get( 'EnhancedUploadOverrideStandardUpload' ) ) {
			$specialPages['Upload'] = EnhancedUpload::class;
		}
		return true;
	}
}
