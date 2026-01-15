<?php

namespace MediaWiki\Extension\NotifyMe;

class Extension {
	public static function onRegistration() {
		\mwsInitComponents();

		$GLOBALS['wgMWStakeNotificationEventConsumers'][] = [ 'class' => NotificationEventConsumer::class,
			'services' => [
				'NotifyMe.Store',
				'ProcessManager',
				'NotifyMe.Logger',
				'NotifyMe._EventProvider',
				'UserFactory'
			] ];
	}
}
