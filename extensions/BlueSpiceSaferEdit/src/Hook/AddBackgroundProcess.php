<?php

namespace BlueSpice\SaferEdit\Hook;

use BlueSpice\SaferEdit\RunJobsTriggerHandler\DeleteOldEntries;

class AddBackgroundProcess {

	/**
	 *
	 * @param array &$handlers
	 * @return bool
	 */
	public static function callback( &$handlers ) {
		$handlers[DeleteOldEntries::HANDLER_KEY] = [
			'class' => DeleteOldEntries::class,
			'services' => [
				'DBLoadBalancer',
			],
		];

		return true;
	}
}
