<?php

namespace MediaWiki\Extension\NotifyMe;

use InvalidArgumentException;
use MediaWiki\Extension\NotifyMe\Channel\WebChannel;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;
use MWStake\MediaWiki\Component\Events\ForcedEvent;
use MWStake\MediaWiki\Component\Events\INotificationEvent;
use MWStake\MediaWiki\Component\Events\NotifyAgentEvent;

final class SubscriberManager {
	/** @var ISubscriberProvider[] */
	private $providers;

	/**
	 * @param array $providers
	 */
	public function __construct( array $providers ) {
		$this->providers = $providers;
	}

	/**
	 * @param INotificationEvent $event
	 * @param IChannel $channel
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getSubscribers( INotificationEvent $event, IChannel $channel ): array {
		$subscribers = [];
		if ( $event instanceof ForcedEvent && $channel instanceof WebChannel ) {
			// Only send mandatory notifications to "primary" channel, which is "web"
			// Not generic though
			return $this->getMandatorySubscribers( $event );
		}
		foreach ( $this->providers as $key => $provider ) {
			$subscribers = $this->merge( $subscribers, $provider->getSubscribers( $event, $channel ), $key );
		}

		if ( !empty( $subscribers ) ) {
			if ( $event->getPresetSubscribers() !== null ) {
				// On self-subscribing events, group of people to notify is already set,
				// but we still need to filter out any user that is not subscribed
				$subscribers = $this->fromSubset( $subscribers, $event->getPresetSubscribers() );
			}
		}

		if ( !( $event instanceof NotifyAgentEvent ) ) {
			$subscribers = $this->removeAgent( $subscribers, $event );
		}
		return array_values( $subscribers );
	}

	/**
	 * For mandatory events, we notify all users that event has pre-set,
	 * even if user opted-out of receiving notifications
	 * @param ForcedEvent $event
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function getMandatorySubscribers( ForcedEvent $event ): array {
		$subscribers = $event->getPresetSubscribers();
		if ( empty( $subscribers ) ) {
			throw new \Exception( 'Mandatory events must provide preset subscribers' );
		}
		$mandatoryUsers = [];
		foreach ( $subscribers as $user ) {
			$mandatoryUsers[$user->getId()] = [
				'user' => $user,
				'providers' => []
			];
		}
		return $this->removeAgent( $mandatoryUsers, $event );
	}

	/**
	 * @param string $providerName
	 *
	 * @return ISubscriberProvider
	 */
	public function getProvider( string $providerName ): ISubscriberProvider {
		if ( isset( $this->providers[$providerName] ) ) {
			return $this->providers[$providerName];
		}
		throw new InvalidArgumentException( "Provider $providerName not found" );
	}

	/**
	 * Add new users to the list of subscribers
	 *
	 * @param array $a
	 * @param array $b
	 * @param string $providerName
	 *
	 * @return array
	 */
	private function merge( array $a, array $b, string $providerName ): array {
		foreach ( $b as $user ) {
			if ( !$user instanceof UserIdentity || !$user->isRegistered() ) {
				continue;
			}
			foreach ( $a as $userId => &$userData ) {
				if ( $user->getId() === $userId ) {
					$userData['providers'][] = $providerName;
					continue 2;
				}
			}
			$a[$user->getId()] = [
				'user' => $user,
				'providers' => [ $providerName ],
			];
		}

		return $a;
	}

	/**
	 * @param array $users
	 * @param INotificationEvent $event
	 *
	 * @return array
	 */
	private function removeAgent( array $users, INotificationEvent $event ): array {
		$agent = $event->getAgent();
		return array_filter( $users, static function ( $userId ) use ( $agent ) {
			return $userId !== $agent->getId();
		}, ARRAY_FILTER_USE_KEY );
	}

	/**
	 * @param array $users
	 * @param array $subset
	 *
	 * @return array
	 */
	private function fromSubset( array $users, array $subset ): array {
		$subset = array_map( static function ( User $user ) {
			return $user->getId();
		}, $subset );
		$subset = array_flip( $subset );
		return array_filter( $users, static function ( $userId ) use ( $subset ) {
			return isset( $subset[$userId] );
		}, ARRAY_FILTER_USE_KEY );
	}
}
