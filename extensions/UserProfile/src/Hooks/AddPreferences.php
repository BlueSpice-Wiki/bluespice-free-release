<?php

namespace MediaWiki\Extension\UserProfile\Hooks;

use MediaWiki\Html\Html;
use MediaWiki\Message\Message;
use MediaWiki\Preferences\Hook\GetPreferencesHook;

class AddPreferences implements GetPreferencesHook {

	/**
	 * @inheritDoc
	 */
	public function onGetPreferences( $user, &$preferences ) {
		$preferences['user-profile-mail-public'] = [
			'section' => 'personal/email',
			'type' => 'check',
			'label-message' => 'userprofile-pref-mail-public',
		];
		$preferences['userimage-profileimage'] = [
			'raw' => true,
			'type' => 'info',
			'label-message' => 'userprofile-pref-userimage',
			'default' => Html::element(
				'a',
				[
					'href' => $user->getUserPage()->getLocalURL(),
				],
				Message::newFromKey( 'userprofile-pref-userimage-link' )->parse()
			),
			'section' => 'personal/info'
		];
	}
}
