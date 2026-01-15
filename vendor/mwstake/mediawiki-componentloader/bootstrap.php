<?php

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENTLOADER_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENTLOADER_VERSION', '1.0.1' );

// phpcs:ignore MediaWiki.NamingConventions.PrefixedGlobalFunctions.wfPrefix
function mwsInitComponents() {
	MWStake\MediaWiki\ComponentLoader\Bootstrapper::getInstance()->init();
}
