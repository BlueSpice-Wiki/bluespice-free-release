<?php

namespace BlueSpice\CountThings\Hook;

use BlueSpice\CountThings\Tag\CountArticles;
use BlueSpice\CountThings\Tag\CountCharacters;
use BlueSpice\CountThings\Tag\CountFiles;
use BlueSpice\CountThings\Tag\CountUsers;
use MWStake\MediaWiki\Component\GenericTagHandler\Hook\MWStakeGenericTagHandlerInitTagsHook;

class RegisterTags implements MWStakeGenericTagHandlerInitTagsHook {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeGenericTagHandlerInitTags( array &$tags ) {
		$tags[] = new CountArticles();
		$tags[] = new CountCharacters();
		$tags[] = new CountFiles();
		$tags[] = new CountUsers();
	}
}
