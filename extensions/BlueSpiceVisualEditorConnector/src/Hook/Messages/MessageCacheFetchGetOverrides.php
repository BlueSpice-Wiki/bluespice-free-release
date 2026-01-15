<?php

namespace BlueSpice\VisualEditorConnector\Hook\Messages;

use MediaWiki\Cache\Hook\MessageCacheFetchOverridesHook;

class MessageCacheFetchGetOverrides implements MessageCacheFetchOverridesHook {

	/**
	 *
	 * @inheritDoc
	 */
	public function onMessageCacheFetchOverrides( array &$keys ): void {
		$saveLabelCallback = static function (): string {
			return 'bs-visualeditor-save-label';
		};
		$overrideVEKeys = [
			'savechanges-start',
			'savearticle-start',
			'visualeditor-savedialog-label-save-short-start',
			'savechanges'
		];
		foreach ( $overrideVEKeys as $key ) {
			$keys[ $key ] = $saveLabelCallback;
		}
	}
}
