<?php

namespace MediaWiki\Extension\NotifyMe;

use MediaWiki\Config\Config;
use MediaWiki\User\Options\UserOptionsManager;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\Delivery\IChannel;

/**
 * This class sits on top of SubscriptionProviders
 * It handles the configuration of the user's subscription to notifications
 * by storing and retrieving the configuration from the user's preferences
 *
 * Individual SubscriptionProviders use the configuration provided by this class
 * to determine whether a user is subscribed to a particular notification
 */
class SubscriptionConfigurator {
	/** @var ChannelFactory */
	private $channelFactory;
	/** @var UserOptionsManager */
	private $userOptionsManager;
	/** @var BucketProvider */
	private $bucketProvider;

	/** @var Config */
	private $mainConfig;

	/**
	 * @param ChannelFactory $channelFactory
	 * @param UserOptionsManager $userOptionsManager
	 * @param BucketProvider $bucketProvider
	 * @param Config $mainConfig
	 */
	public function __construct(
		ChannelFactory $channelFactory, UserOptionsManager $userOptionsManager,
		BucketProvider $bucketProvider, Config $mainConfig
	) {
		$this->channelFactory = $channelFactory;
		$this->userOptionsManager = $userOptionsManager;
		$this->bucketProvider = $bucketProvider;
		$this->mainConfig = $mainConfig;
	}

	/**
	 * @param UserIdentity $user
	 *
	 * @return array
	 */
	public function getConfiguration( UserIdentity $user ): array {
		$config = $this->userOptionsManager->getOption( $user, 'ext-notification-subscriptions' );
		if ( !$config ) {
			return [];
		}
		return json_decode( $config, true );
	}

	/**
	 * @return array
	 */
	public function getChannelLabels(): array {
		$labels = [];
		foreach ( $this->channelFactory->getChannels() as $channel ) {
			$labels[$channel->getKey()] = [
				'label' => $channel->getLabel()->text(),
				'icon' => $channel->getKey() . '-icon',
			];
		}
		return $labels;
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	public function getBucketData(): array {
		return $this->bucketProvider->getBucketLabels();
	}

	/**
	 * @return array
	 */
	public function getEventData(): array {
		return $this->bucketProvider->getEventDescription();
	}

	/**
	 * @param UserIdentity $user
	 * @param IChannel $channel
	 *
	 * @return array
	 */
	public function getChannelConfiguration( UserIdentity $user, IChannel $channel ): array {
		$configuration = $this->getConfiguration( $user );
		if (
			!isset( $configuration['channels'] ) ||
			!isset( $configuration['channels'][$channel->getKey()] )
		) {
			return [];
		}
		return $configuration['channels'][$channel->getKey()];
	}

	/**
	 * @return string
	 */
	public function getDefaultValue(): string {
		$staticOptions = $this->mainConfig->get( 'DefaultUserOptions' );
		if ( isset( $staticOptions['ext-notification-subscriptions'] ) ) {
			$value = $staticOptions['ext-notification-subscriptions'];
			if ( is_string( $value ) ) {
				$parsed = json_decode( $value, true );
				if ( $parsed !== null ) {
					return $value;
				}
			}
		}
		$defaultOptions = [
			'subscriptions' => [
				[
					'setType' => 'watchlist',
					'set' => [],
					'bucket' => 'content-high-freq',
					'channels' => [ 'web', 'email' ]
				],
			],
		];
		foreach ( $this->channelFactory->getChannels() as $channel ) {
			// Enable all channels by default
			$channelConfig = $channel->getDefaultConfiguration();
			if ( $channelConfig ) {
				$defaultOptions['channels'][$channel->getKey()] = $channelConfig;
			}
		}

		return json_encode( $defaultOptions );
	}
}
