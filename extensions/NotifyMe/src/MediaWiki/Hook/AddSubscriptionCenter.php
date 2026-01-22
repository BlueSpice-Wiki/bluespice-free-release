<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\Hook;

use MediaWiki\Extension\NotifyMe\MediaWiki\Html\NotificationsSubscriptionsElement;
use MediaWiki\Extension\NotifyMe\SubscriberManager;
use MediaWiki\Extension\NotifyMe\SubscriptionConfigurator;
use MediaWiki\HTMLForm\HTMLForm;
use MediaWiki\Preferences\Hook\GetPreferencesHook;
use MediaWiki\User\Hook\UserGetDefaultOptionsHook;
use MediaWiki\User\User;

class AddSubscriptionCenter implements GetPreferencesHook, UserGetDefaultOptionsHook {
	/** @var SubscriptionConfigurator */
	private $configurator;

	/** @var SubscriberManager */
	private $manager;

	/**
	 * @param SubscriptionConfigurator $configurator
	 * @param SubscriberManager $manger
	 */
	public function __construct( SubscriptionConfigurator $configurator, SubscriberManager $manger ) {
		$this->configurator = $configurator;
		$this->manager = $manger;
	}

	/**
	 * @param User $user
	 * @param array &$preferences
	 *
	 * @return bool|void
	 */
	public function onGetPreferences( $user, &$preferences ) {
		$manualProvider = $this->manager->getProvider( 'manual-subscriptions' );
		$additionalModules = $manualProvider->getRLModulesFromSets();
		$config = $this->configurator->getConfiguration( $user );

		$validSubscriptions = [];
		foreach ( $config['subscriptions'] ?? [] as $item ) {
			if ( $manualProvider->isValidSet( $item['setType'] ) ) {
				$validSubscriptions[] = $item;
			}
		}
		$config['subscriptions'] = $validSubscriptions;

		HTMLForm::$typeMappings['notifications-subscriptions'] = NotificationsSubscriptionsElement::class;
		$preferences['ext-notification-subscriptions'] = [
			'type' => 'notifications-subscriptions',
			'section' => 'notifications/subs',
			'rl-modules' => array_merge( [ 'ext.notifyme.subscription-preferences' ], $additionalModules ),
			'value' => [
				'configuration' => $config,
				'bucketData' => $this->configurator->getBucketData(),
				'eventData' => $this->configurator->getEventData(),
				'channelLabels' => $this->configurator->getChannelLabels(),
			]
		];
	}

	/**
	 * @param array &$defaultOptions
	 *
	 * @return void
	 */
	public function onUserGetDefaultOptions( &$defaultOptions ) {
		$defaultOptions[ 'ext-notification-subscriptions'] = $this->configurator->getDefaultValue();
	}
}
