<?php

namespace MediaWiki\Extension\FlexiSkin\HookHandler;

use MediaWiki\Extension\FlexiSkin\IFlexiSkin;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

class WhitelistImages {

	/**
	 * @param Title $title
	 * @param string $path
	 * @param string $name
	 * @param bool &$whitelisted
	 * @return bool
	 */
	public function onCheckFileWhitelisted( Title $title, string $path, string $name, bool &$whitelisted ): bool {
		$activeSkin = MediaWikiServices::getInstance()->getService( 'FlexiSkinManager' )->getActive();
		if ( !( $activeSkin instanceof IFlexiSkin ) ) {
			return true;
		}
		$config = $activeSkin->getConfig();
		$logoFilename = $config['images']['logo']['filename'] ?? null;
		$faviconFilename = $config['images']['favicon']['filename'] ?? null;
		if ( ( $logoFilename !== null && $name === $logoFilename ) ||
			( $faviconFilename !== null && $name === $faviconFilename ) ) {
			$whitelisted = true;
			return false;
		}
		return true;
	}
}
