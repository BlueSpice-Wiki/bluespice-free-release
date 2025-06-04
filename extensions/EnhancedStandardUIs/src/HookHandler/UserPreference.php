<?php

namespace MediaWiki\Extension\EnhancedStandardUIs\HookHandler;

use MediaWiki\Preferences\Hook\GetPreferencesHook;

class UserPreference implements GetPreferencesHook {

	/**
	 *
	 * @inheritDoc
	 */
	public function onGetPreferences( $user, &$preferences ) {
		$api = [ 'type' => 'api' ];
		$preferences[ 'history-show-revision' ] = $api;
		$preferences[ 'history-show-author' ] = $api;
		$preferences[ 'history-show-diff' ] = $api;
		$preferences[ 'history-show-size' ] = $api;
		$preferences[ 'history-show-summary' ] = $api;
		$preferences[ 'history-show-tags' ] = $api;
		$preferences[ 'filelist-show-preview' ] = $api;
		$preferences[ 'filelist-show-title' ] = $api;
		$preferences[ 'filelist-show-author' ] = $api;
		$preferences[ 'filelist-show-formatted_ts' ] = $api;
		$preferences[ 'filelist-show-file_extension' ] = $api;
		$preferences[ 'filelist-show-file_size' ] = $api;
		$preferences[ 'filelist-show-categories' ] = $api;
		$preferences[ 'filelist-show-comment' ] = $api;
		$preferences[ 'allpages-show-talk' ] = $api;
		$preferences[ 'allpages-show-non-content' ] = $api;
		$preferences[ 'allpages-show-redirect' ] = $api;
	}
}
