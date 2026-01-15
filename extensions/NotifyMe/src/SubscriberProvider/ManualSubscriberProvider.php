<?php

namespace MediaWiki\Extension\NotifyMe\SubscriberProvider;

use Exception;
use MediaWiki\Block\AbstractBlock;
use MediaWiki\Extension\NotifyMe\BucketProvider;
use MediaWiki\Extension\NotifyMe\ISubscriberProvider;
use MediaWiki\Extension\NotifyMe\SubscriberProvider\ManualProvider\ISubscriptionSet;
use MediaWiki\Extension\NotifyMe\SubscriptionConfigurator;
use MediaWiki\Message\Message;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\User\User;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;
use MWStake\MediaWiki\Component\Events\INotificationEvent;
use MWStake\MediaWiki\Component\Events\ITitleEvent;
use MWStake\MediaWiki\Component\Events\Notification;
use Wikimedia\ObjectFactory\ObjectFactory;
use Wikimedia\Rdbms\LoadBalancer;

class ManualSubscriberProvider implements ISubscriberProvider {

	/** @var UserFactory */
	private $userFactory;
	/** @var LoadBalancer */
	private $loadBalancer;
	/** @var SubscriptionConfigurator */
	private $subscriptionConfigurator;
	/** @var BucketProvider */
	private $bucketProvider;

	/** @var array */
	private $subscriptionSets;

	/**
	 * @param UserFactory $userFactory
	 * @param LoadBalancer $lb
	 * @param SubscriptionConfigurator $configurator
	 * @param ObjectFactory $objectFactory
	 * @param BucketProvider $bucketProvider
	 * @param string $subscriptionSetsAttribute
	 */
	public function __construct(
		UserFactory $userFactory, LoadBalancer $lb, SubscriptionConfigurator $configurator,
		ObjectFactory $objectFactory, BucketProvider $bucketProvider, string $subscriptionSetsAttribute
	) {
		$this->userFactory = $userFactory;
		$this->loadBalancer = $lb;
		$this->subscriptionConfigurator = $configurator;
		$this->bucketProvider = $bucketProvider;
		$this->makeSetProviders( $subscriptionSetsAttribute, $objectFactory );
	}

	/**
	 * Only exists to allow unittest to set the configuration
	 * @param array $providers
	 *
	 * @return void
	 */
	public function setSetProviders( array $providers ) {
		if ( !defined( 'MW_PHPUNIT_TEST' ) ) {
			throw new \LogicException( 'This method is only for testing' );
		}
		$this->subscriptionSets = $providers;
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'manual-subscriptions';
	}

	/**
	 * Get RL modules from the SubscriptionSets, to provider UI integration
	 *
	 * @return array
	 */
	public function getRLModulesFromSets(): array {
		$modules = [];
		/** @var ISubscriptionSet $set */
		foreach ( $this->subscriptionSets as $set ) {
			$modules[] = $set->getClientSideModule();
		}
		return $modules;
	}

	/**
	 * @param INotificationEvent $event
	 * @param IChannel $channel
	 *
	 * @return array|UserIdentity[]
	 * @throws Exception
	 */
	public function getSubscribers( INotificationEvent $event, IChannel $channel ): array {
		$allUsers = $this->getAllUsers();
		$subscribers = [];
		foreach ( $allUsers as $user ) {
			if ( $user->getBlock() instanceof AbstractBlock ) {
				continue;
			}

			if ( $this->isSubscribed( $user, $event, $channel ) ) {
				$subscribers[] = $user;
			}
		}
		return $subscribers;
	}

	/**
	 * @param UserIdentity $user
	 * @param INotificationEvent $event
	 * @param IChannel $channel
	 *
	 * @return bool
	 * @throws Exception
	 */
	private function isSubscribed( UserIdentity $user, INotificationEvent $event, IChannel $channel ): bool {
		$data = $this->subscriptionConfigurator->getConfiguration( $user );
		if ( !( $event instanceof ITitleEvent ) ) {
			// We cannot check for non-title events, allow all
			return true;
		}
		if ( $this->bucketProvider->hasMandatoryBuckets( $event ) ) {
			// Buckets user cannot opt out of
			return true;
		}
		// User has not sets, no subscription
		if ( !isset( $data['subscriptions'] ) ) {
			return false;
		}

		// Retrieve subscription sets for this user - these correspond to the "tiles" in the UI
		$subscriptions = $data['subscriptions'];
		foreach ( $subscriptions as $subscription ) {
			// If requested channel is not enabled by the set, skip
			if ( !in_array( $channel->getKey(), $subscription['channels'] ) ) {
				continue;
			}
			// If the event is not in the bucket that this set refers to, skip
			if ( !$this->doesSetHandleBucketOfEvent( $subscription['bucket'], $event ) ) {
				continue;
			}
			// Get set handler - this is the class that knows how to check if the user is subscribed
			// based on particular data related to that set type
			$set = $this->getSetForSubscriptionData( $subscription['setType'] );
			if ( !$set ) {
				// No handler for this set type, skip
				continue;
			}
			if ( $set->isSubscribed( $subscription['set'] ?? [], $event, $user ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param string $bucket
	 * @param ITitleEvent $event
	 *
	 * @return bool
	 * @throws Exception
	 */
	private function doesSetHandleBucketOfEvent( string $bucket, ITitleEvent $event ): bool {
		return in_array( $bucket, $this->bucketProvider->getEventBuckets( $event ) );
	}

	/**
	 * @return User[]
	 */
	private function getAllUsers() {
		// TODO: Cache/improve
		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		$res = $dbr->select(
			'user',
			'user_id',
			[],
			__METHOD__
		);
		$users = [];
		foreach ( $res as $row ) {
			$users[] = $this->userFactory->newFromId( $row->user_id );
		}
		return $users;
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription( Notification $notification ): Message {
		if ( $this->bucketProvider->hasMandatoryBuckets( $notification->getEvent() ) ) {
			return Message::newFromKey( 'notifyme-subscriber-provider-mandatory-desc' );
		}
		return Message::newFromKey( 'notifyme-subscriber-provider-preferences-desc' );
	}

	/**
	 * @inheritDoc
	 */
	public function getConfigurationLink(): ?string {
		$sp = SpecialPage::getTitleFor( 'Preferences', false, 'mw-prefsection-notifications' );
		return $sp->getFullURL();
	}

	/**
	 * @param string $setName
	 * @return bool
	 */
	public function isValidSet( string $setName ): bool {
		return isset( $this->subscriptionSets[$setName] );
	}

	/**
	 * @param string $subscriptionSetsAttribute
	 * @param ObjectFactory $of
	 *
	 * @return void
	 */
	private function makeSetProviders( string $subscriptionSetsAttribute, ObjectFactory $of ) {
		$attribute = ExtensionRegistry::getInstance()->getAttribute( $subscriptionSetsAttribute );
		if ( !$attribute ) {
			return;
		}

		$this->subscriptionSets = [];
		foreach ( $attribute as $key => $spec ) {
			$instance = $of->createObject( $spec );
			if ( !( $instance instanceof ISubscriptionSet ) ) {
				throw new \RuntimeException( "Subscription set $key must implement ISubscriptionSet" );
			}
			$this->subscriptionSets[$key] = $instance;
		}
	}

	/**
	 * @param string $setType
	 *
	 * @return ISubscriptionSet|null
	 */
	private function getSetForSubscriptionData( string $setType ): ?ISubscriptionSet {
		return $this->subscriptionSets[$setType] ?? null;
	}
}
