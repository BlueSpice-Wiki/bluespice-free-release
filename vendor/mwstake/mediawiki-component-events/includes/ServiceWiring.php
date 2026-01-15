<?php

use MWStake\MediaWiki\Component\Events\Notifier;

return [
	'MWStake.Notifier' => static function ( \MediaWiki\MediaWikiServices $services ) {
		$consumers = [];
		foreach ( $GLOBALS['wgMWStakeNotificationEventConsumers'] as $consumerSpec ) {
			$consumer = $services->getObjectFactory()->createObject( $consumerSpec );
			if ( $consumer instanceof \MWStake\MediaWiki\Component\Events\INotificationEventConsumer ) {
				$consumers[] = $consumer;
			}
		}
		return new Notifier( $consumers, $services->getDBLoadBalancerFactory(), $services->getHookContainer() );
	}
];
