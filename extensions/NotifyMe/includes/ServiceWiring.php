<?php

use MediaWiki\Extension\NotifyMe\BucketProvider;
use MediaWiki\Extension\NotifyMe\Channel\Email\MailContentProvider;
use MediaWiki\Extension\NotifyMe\ChannelFactory;
use MediaWiki\Extension\NotifyMe\EventFactory;
use MediaWiki\Extension\NotifyMe\EventProvider;
use MediaWiki\Extension\NotifyMe\ISubscriberProvider;
use MediaWiki\Extension\NotifyMe\NotificationSerializer;
use MediaWiki\Extension\NotifyMe\NotificationTester;
use MediaWiki\Extension\NotifyMe\Storage\NotificationStore;
use MediaWiki\Extension\NotifyMe\Storage\WebNotificationQueryStore;
use MediaWiki\Extension\NotifyMe\SubscriberManager;
use MediaWiki\Extension\NotifyMe\SubscriptionConfigurator;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Registration\ExtensionRegistry;
use Psr\Log\LoggerAwareInterface;

return [
	'NotifyMe.Logger' => static function ( MediaWikiServices $services ) {
		return LoggerFactory::getInstance( 'notifications' );
	},
	'NotifyMe.ChannelFactory' => static function ( MediaWikiServices $services ) {
		// Temporary compat warning
		$compatAttribute = ExtensionRegistry::getInstance()->getAttribute( 'NotificationsChannels' );
		if ( !empty( $compatAttribute ) ) {
			throw new Exception( 'NotificationsChannels is no longer supported, convert to NotifyMeChannels' );
		}
		return new ChannelFactory(
			ExtensionRegistry::getInstance()->getAttribute( 'NotifyMeChannels' ),
			$services->getObjectFactory()
		);
	},
	'NotifyMe.Serializer' => static function ( MediaWikiServices $services ) {
		return new NotificationSerializer(
			$services->getUserFactory(),
			$services->getService( 'NotifyMe.ChannelFactory' ),
			$services->getService( 'NotifyMe.SubscriberManager' ),
			$services->getLanguageFactory(),
			$services->getUserOptionsLookup(),
			$services->getContentLanguage(),
			$services->getService( 'NotifyMe.EventFactory' )
		);
	},
	'NotifyMe.Store' => static function ( MediaWikiServices $services ) {
		return new NotificationStore(
			$services->getDBLoadBalancer(),
			$services->getService( 'NotifyMe.Serializer' ),
			$services->getService( 'NotifyMe._EventProvider' ),
		);
	},
	'NotifyMe.SubscriberManager' => static function ( MediaWikiServices $services ) {
		// Temporary compat warning
		$compatAttribute = ExtensionRegistry::getInstance()->getAttribute( 'NotificationsSubscriberProviders' );
		if ( !empty( $compatAttribute ) ) {
			throw new Exception(
				'NotificationsSubscriberProviders is no longer supported, convert to NotifyMeSubscriberProviders'
			);
		}
		$registry = ExtensionRegistry::getInstance()->getAttribute( 'NotifyMeSubscriberProviders' );
		$providers = [];
		foreach ( $registry as $provider ) {
			$provider = $services->getObjectFactory()->createObject( $provider );
			if ( $provider instanceof ISubscriberProvider ) {
				$providers[$provider->getKey()] = $provider;
			}
			if ( $provider instanceof LoggerAwareInterface ) {
				$provider->setLogger( $services->getService( 'NotifyMe.Logger' ) );
			}
		}
		return new SubscriberManager( $providers );
	},
	'NotifyMe.SubscriptionConfigurator' => static function ( MediaWikiServices $services ) {
		return new SubscriptionConfigurator(
			$services->getService( 'NotifyMe.ChannelFactory' ),
			$services->getUserOptionsManager(),
			$services->getService( 'NotifyMe.Buckets' ),
			$services->getMainConfig()
		);
	},
	'NotifyMe.MailContentProvider' => static function ( MediaWikiServices $services ) {
		return new MailContentProvider(
			$services->getDBLoadBalancer(),
			$services->getTitleFactory(),
			$services->getMainConfig(),
			$services->getRevisionLookup(),
			$services->getParserFactory(),
			$services->getUserOptionsLookup(),
			$services->getContentLanguage()
		);
	},
	'NotifyMe.WebQueryStore' => static function ( MediaWikiServices $services ) {
		return new WebNotificationQueryStore(
			$services->getDBLoadBalancer(),
			$services->getWikiPageFactory(),
			$services->getTitleFactory(),
			$services->getContentLanguage(),
			$services->getService( 'NotifyMe.Buckets' ),
			$services->getHookContainer()
		);
	},
	'NotifyMe.Buckets' => static function ( MediaWikiServices $services ) {
		// Temporary compat warning
		$compatAttribute = ExtensionRegistry::getInstance()->getAttribute( 'NotificationsBuckets' );
		if ( !empty( $compatAttribute ) ) {
			throw new Exception( 'NotificationsBuckets is no longer supported, convert to NotifyMeBuckets' );
		}
		return new BucketProvider(
			ExtensionRegistry::getInstance()->getAttribute( 'NotifyMeBuckets' ),
			$services->getService( 'NotifyMe._EventProvider' )
		);
	},
	'NotifyMe.EventFactory' => static function ( MediaWikiServices $services ) {
		return new EventFactory(
			$services->getService( 'NotifyMe._EventProvider' ),
			$services->getObjectFactory()
		);
	},
	'NotifyMe.Tester' => static function ( MediaWikiServices $services ) {
		return new NotificationTester(
			$services->getService( 'NotifyMe.EventFactory' ),
			$services,
			$services->getService( 'MWStake.Notifier' ),
		);
	},
	'NotifyMe._EventProvider' => static function ( MediaWikiServices $services ) {
		// Temporary compat warning
		$compatAttribute = ExtensionRegistry::getInstance()->getAttribute( 'NotificationsEvents' );
		if ( !empty( $compatAttribute ) ) {
			throw new Exception( 'NotificationsEvents is no longer supported, convert to NotifyMeEvents' );
		}
		return new EventProvider(
			ExtensionRegistry::getInstance()->getAttribute( 'NotifyMeEvents' ),
			$services->getHookContainer()
		);
	},
];
