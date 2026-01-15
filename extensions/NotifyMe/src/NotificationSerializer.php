<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\NotifyMe;

use DateTime;
use Exception;
use InvalidArgumentException;
use MediaWiki\Extension\NotifyMe\Channel\WebChannel;
use MediaWiki\Extension\NotifyMe\Grouping\NotificationGroup;
use MediaWiki\Language\Language;
use MediaWiki\Languages\LanguageFactory;
use MediaWiki\Message\Message;
use MediaWiki\User\Options\UserOptionsLookup;
use MediaWiki\User\User;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\BotAgent;
use MWStake\MediaWiki\Component\Events\Delivery\NotificationStatus;
use MWStake\MediaWiki\Component\Events\EventLink;
use MWStake\MediaWiki\Component\Events\INotificationEvent;
use MWStake\MediaWiki\Component\Events\Notification;
use RuntimeException;
use stdClass;
use Throwable;

final class NotificationSerializer {
	/** @var UserFactory */
	private $userFactory;
	/** @var ChannelFactory */
	private $channelFactory;
	/** @var SubscriberManager */
	private $subscriptionManager;
	/** @var LanguageFactory */
	private $languageFactory;
	/** @var UserOptionsLookup */
	private $userOptionsLookup;
	/** @var Language */
	private $contentLang;
	/** @var EventFactory */
	private $eventFactory;

	/**
	 * @var array[]
	 */
	private $schema = [
		'notification' => [
			'entity_type' => [
				'type' => 'string',
				'public' => false
			],
			'id' => [
				'type' => 'string',
				'public' => false
			],
			'message' => [
				'type' => 'string',
				'desc' => 'notifyme-schema-message',
				'example' => '{{{message}}}'
			],
			'icon' => [
				'type' => 'string',
				'public' => false
			],
			'links_intro' => [
				'type' => 'string',
				'desc' => 'notifyme-schema-link-intro',
				'example' => '{{{link_intro}}}'
			],
			'links' => [
				'type' => 'array',
				'desc' => 'notifyme-schema-links',
				'example' => "{{#links}}\n<ul>\n\t<li><a href='{{url}}'>{{label}}</a></li>\n</ul>\n{{/links}}",
				'items' => [
					'primary' => [
						'type' => 'boolean',
						'desc' => 'notifyme-schema-links-primary',
					],
					'label' => [
						'type' => 'string',
						'desc' => 'notifyme-schema-links-label',
					],
					'url' => [
						'type' => 'string',
						'desc' => 'notifyme-schema-links-url',
					]
				],
			],
			'agent' => [
				'type' => 'array',
				'desc' => 'notifyme-schema-agent',
				'example' => "{{#agent}}<a href='{{user_page}}'>{{display_name}} ({{username}})</a>{{/agent}}",
				'items' => [
					'display_name' => [
						'type' => 'string',
						'desc' => 'notifyme-schema-target-user-display-name',
					],
					'user_page' => [
						'type' => 'string',
						'desc' => 'notifyme-schema-links-user-page',
					],
					'username' => [
						'type' => 'string',
						'desc' => 'notifyme-schema-target-user-username',
					]
				],
			],
			'agent_is_bot' => [
				'type' => 'boolean',
				'desc' => 'notifyme-schema-agent-is-bot',
				'example' => '{{#agent_is_bot}}no_agent{{/agent_is_bot}}'
			],
			'user_timestamp' => [
				'type' => 'string',
				'desc' => 'notifyme-schema-user-timestamp',
				'example' => '{{{user_timestamp}}}'
			],
			'timestamp' => [
				'type' => 'string',
				'public' => false,
			],
			'status' => [
				'type' => 'string',
				'public' => false
			],
			'target_user' => [
				'type' => 'array',
				'desc' => 'notifyme-schema-target-user',
				'example' => "{{#target_user}}<a href='{{user_page}}'>{{display_name}} " .
					"({{username}})</a>{{/target_user}}",
				'items' => [
					'display_name' => [
						'type' => 'string',
						'desc' => 'notifyme-schema-target-user-display-name',
					],
					'user_page' => [
						'type' => 'string',
						'desc' => 'notifyme-schema-links-user-page',
					],
					'username' => [
						'type' => 'string',
						'desc' => 'notifyme-schema-target-user-username',
					]
				],
			],
			'channel' => [
				'type' => 'string',
				'public' => false
			],
			'source_providers' => [
				'type' => 'array',
				'desc' => 'notifyme-schema-source-providers',
				'example' => "{{#source_providers}}\n<ul>\n\t<li>{{key}}</li>\n\t<li>{{description}}</li>" .
					"\n\t<li>{{link}}</li>\n</ul>\n{{/source_providers}}",
				'items' => [
					'key' => [
						'type' => 'string',
						'public' => false,
					],
					'description' => [
						'type' => 'string',
						'desc' => 'notifyme-schema-source-providers-description',
					],
					'link' => [
						'type' => 'string',
						'desc' => 'notifyme-schema-source-providers-link',
					],
				],
			]
		],
		'group' => [
			'entity_type' => [
				'type' => 'string',
				'public' => false
			],
			'message' => [
				'type' => 'string',
				'desc' => 'notifyme-schema-group-message',
				'example' => '{{{message}}}'
			],
			'icon' => [
				'type' => 'string',
				'public' => false
			],
			'timestamp' => [
				'type' => 'string',
				'public' => false,
			],
			'count' => [
				'type' => 'integer',
				'desc' => 'notifyme-schema-group-count',
				'example' => '{{{count}}}'
			],
			'target_user' => [
				'type' => 'string',
				'desc' => 'notifyme-schema-target-user',
				'example' => '{{{target_user}}}'
			],
			'notifications' => [
				'type' => 'array',
				'desc' => 'notifyme-schema-group-notifications',
				'example' => "{{#notifications}}{{message}}{{/notifications}}",
				'schemaKey' => 'notification',
			]
		]
	];

	/**
	 * @param UserFactory $userFactory
	 * @param ChannelFactory $channelFactory
	 * @param SubscriberManager $subscriberManager
	 * @param LanguageFactory $languageFactory
	 * @param UserOptionsLookup $userOptionsLookup
	 * @param Language $contentLang
	 * @param EventFactory $eventFactory
	 */
	public function __construct(
		UserFactory $userFactory, ChannelFactory $channelFactory, SubscriberManager $subscriberManager,
		LanguageFactory $languageFactory, UserOptionsLookup $userOptionsLookup, Language $contentLang,
		EventFactory $eventFactory
	) {
		$this->userFactory = $userFactory;
		$this->channelFactory = $channelFactory;
		$this->subscriptionManager = $subscriberManager;
		$this->languageFactory = $languageFactory;
		$this->userOptionsLookup = $userOptionsLookup;
		$this->contentLang = $contentLang;
		$this->eventFactory = $eventFactory;
	}

	/**
	 * @param Notification $notification
	 * @param UserIdentity $user For which user to serialize the notification
	 *
	 * @return array
	 * @throws Exception
	 */
	public function serializeForOutput( Notification $notification, UserIdentity $user ): array {
		$lang = $this->getUserLanguage( $user );
		$channel = $notification->getChannel();
		$message = $notification->getEvent()->getMessage( $channel )->inLanguage( $lang )->parse();
		if ( $channel instanceof WebChannel ) {
			$message = $this->prepareMessageForWeb( $message );
		}
		$data = [
			'entity_type' => 'single_notification',
			'id' => $notification->getId(),
			'message' => $message,
			'links_intro' => $notification->getEvent()->getLinksIntroMessage( $channel ) ?
				$notification->getEvent()->getLinksIntroMessage( $channel )->inLanguage( $lang )->parse() : '',
			'links' => $this->serializeLinks( $notification->getEvent()->getLinks( $channel ), $lang ),
			'agent' => $this->getUserOutputInfo( $notification->getEvent()->getAgent() ),
			'agent_is_bot' => $notification->getEvent()->getAgent() instanceof BotAgent,
			'icon' => $notification->getEvent()->getIcon(),
			'user_timestamp' => $lang->userTimeAndDate(
				$notification->getEvent()->getTime()->format( 'YmdHis' ),
				$user,
				[ 'timecorrection' => true ]
			),
			'timestamp' => $notification->getEvent()->getTime()->format( 'c' ),
			'status' => $notification->getStatus()->getStatus(),
			'target_user' => $this->getUserOutputInfo( $user ),
			'channel' => $channel->getKey(),
			'source_providers' => array_map(
				static function ( ISubscriberProvider $provider ) use ( $lang, $notification ) {
					return [
						'key' => $provider->getKey(),
						'description' => $provider->getDescription( $notification )
							->inLanguage( $lang )->parse(),
						'link' => $provider->getConfigurationLink(),
					];
				}, $notification->getSourceProviders()
			),
		];

		$data = array_filter( $data, function ( $value ) {
			return isset( $this->schema['notification'][$value] );
		}, ARRAY_FILTER_USE_KEY );

		$channel->onNotificationOutputSerialized( $notification, $data );

		return $data;
	}

	/**
	 * @param NotificationGroup $group
	 * @param UserIdentity $user For which user to serialize the notification
	 *
	 * @return array
	 * @throws Exception
	 */
	public function serializeNotificationGroupForOutput( NotificationGroup $group, UserIdentity $user ): array {
		$lang = $this->getUserLanguage( $user );
		$count = count( $group->getNotifications() );
		$channel = $group->getNotifications() ? $group->getNotifications()[0]->getChannel() : null;
		if ( $channel === null ) {
			throw new InvalidArgumentException( 'Group has no notifications' );
		}
		$mostRecent = $this->getMostRecentNotification( $group->getNotifications() );
		$message = $group->getEvent()->getGroupMessage( $count, $channel )->inLanguage( $lang )->parse();
		if ( $channel instanceof WebChannel ) {
			$message = $this->prepareMessageForWeb( $message );
		}
		$data = [
			'entity_type' => 'group',
			'message' => $message,
			'icon' => $mostRecent->getEvent()->getIcon(),
			'timestamp' => $mostRecent->getEvent()->getTime()->format( 'c' ),
			'user_timestamp' => $lang->userTimeAndDate(
				$mostRecent->getEvent()->getTime()->format( 'YmdHis' ),
				$user,
				[ 'timecorrection' => true ]
			),
			'count' => $count,
			'target_user' => $this->getUserOutputInfo( $user ),
			'notifications' => array_map( function ( Notification $notification ) use ( $lang, $user ) {
				return $this->serializeForOutput( $notification, $user );
			}, $group->getNotifications() ),
		];
		return array_filter( $data, function ( $value ) {
			return isset( $this->schema['group'][$value] );
		}, ARRAY_FILTER_USE_KEY );
	}

	/**
	 * @param Notification $notification
	 *
	 * @return array
	 */
	public function serialize( Notification $notification ): array {
		return [
			'id' => $notification->getId(),
			'channel' => $notification->getChannel()->getKey(),
			'target_user' => $notification->getTargetUser()->getId(),
			'status' => $notification->getStatus()->jsonSerialize(),
			'source_providers' => array_map( static function ( ISubscriberProvider $provider ) {
				return $provider->getKey();
			}, $notification->getSourceProviders() ),
		];
	}

	/**
	 * @param stdClass $data
	 *
	 * @return Notification
	 */
	public function unserialize( stdClass $data ): Notification {
		$payload = json_decode( $data->ni_payload, true );
		$event = $this->unserializeEvent( $data );
		$channel = $this->channelFactory->getChannel( $payload['channel'] );
		if ( !$channel ) {
			throw new InvalidArgumentException( "Channel {$payload['channel']} not found" );
		}
		$targetUser = $this->userFactory->newFromId( $payload['target_user'] );
		$status = $this->createStatus( $payload['status'] );
		$providers = [];
		foreach ( $payload['source_providers'] as $providerKey ) {
			try {
				$providers[] = $this->subscriptionManager->getProvider( $providerKey );
			} catch ( Exception $e ) {
				// Ignore
			}
		}
		$notification = new Notification( $event, $targetUser, $channel, $status, $providers );
		$notification->setId( (int)$data->ni_id );

		return $notification;
	}

	/**
	 * @param string $type
	 *
	 * @return array|null
	 */
	public function getSchemaFor( $type ): ?array {
		return $this->schema[$type] ?? null;
	}

	/**
	 * @param string $type
	 *
	 * @return array|null
	 */
	public function getPublicSchemaFor( $type ): ?array {
		$schema = $this->getSchemaFor( $type );
		if ( !$schema ) {
			return null;
		}
		return $this->formatSchemaForOutput( $schema );
	}

	/**
	 * @param array $schema
	 *
	 * @return array
	 */
	private function formatSchemaForOutput( array $schema ): array {
		return array_filter( array_map( function ( $value ) {
			if ( isset( $value['public'] ) && !$value['public'] ) {
				return null;
			}
			$value['desc'] = Message::newFromKey( $value['desc'] )->plain();
			$value['example'] = htmlspecialchars( $value['example'] ?? '' );
			if ( isset( $value['schemaKey'] ) ) {
				$value['items'] = $this->getPublicSchemaFor( $value['schemaKey'] );
			} elseif ( $value['type'] === 'array' ) {
				$value['items'] = $this->formatSchemaForOutput( $value['items'] );
			}

			return $value;
		}, $schema ) );
	}

	/**
	 * @param INotificationEvent $event
	 *
	 * @return string
	 * @throws RuntimeException
	 */
	public function serializeEvent( INotificationEvent $event ): string {
		set_error_handler( static function ( $errno, $errstr ) use ( $event ) {
			$class = get_class( $event );
			throw new RuntimeException( "Serialization of event $class failed: $errstr ($errno)" );
		}, E_WARNING | E_NOTICE );

		$serializableSpec = $this->eventFactory->getSerializableSpec( $event );
		if ( $serializableSpec ) {
			$data = serialize( [ 'key' => $event->getKey(), 'spec' => $serializableSpec ] );
		} else {
			$data = serialize( $event );
		}
		$serialized = base64_encode( $data );
		restore_error_handler();
		return $serialized;
	}

	/**
	 * @param stdClass $data
	 *
	 * @return INotificationEvent
	 */
	public function unserializeEvent( stdClass $data ): INotificationEvent {
		try {
			$instance = unserialize( base64_decode( $data->ne_payload ) );
			if ( is_array( $instance ) && isset( $instance['key'] ) && isset( $instance['spec'] ) ) {
				$args = $instance['spec']['args'] ?? [];
				$instance = $this->eventFactory->create( $instance['key'], $args );
				$timestamp = $data->ne_timestamp ?? null;
				if ( !$timestamp ) {
					throw new Exception( "Tried to unserialize event without passing timestamp in the data" );
				}
				$time = DateTime::createFromFormat( 'YmdHis', $timestamp );
				if ( !$time ) {
					throw new Exception( "Failed to parse timestamp $timestamp" );
				}
				$instance->setTime( $time );
			}

			if ( !( $instance instanceof INotificationEvent ) ) {
				throw new InvalidArgumentException(
					"Event $data->ne_key is not an instance of " . INotificationEvent::class
				);
			}
		} catch ( Throwable $e ) {
			$instance = null;
		}
		if ( !( $instance instanceof INotificationEvent ) ) {
			throw new InvalidArgumentException(
				"Event $data->ne_key is not an instance of " . INotificationEvent::class
			);
		}
		return $instance;
	}

	/**
	 * @param array $links
	 * @param Language $lang
	 *
	 * @return array
	 * @throws Exception
	 */
	private function serializeLinks( array $links, Language $lang ): array {
		$isPrimary = true;
		return array_map( static function ( EventLink $link ) use ( $lang, &$isPrimary ) {
			$link = [
				'primary' => $isPrimary,
				'url' => $link->getUrl(),
				'label' => $link->getLabel()->inLanguage( $lang )->parse(),
			];
			$isPrimary = false;
			return $link;
		}, $links );
	}

	/**
	 * @param UserIdentity $user
	 *
	 * @return Language
	 * @throws Exception
	 */
	private function getUserLanguage( UserIdentity $user ): Language {
		$langCode = $this->userOptionsLookup->getOption( $user, 'language' );
		if ( $langCode === null ) {
			$langCode = $this->contentLang;
		}
		return $this->languageFactory->getLanguage( $langCode );
	}

	/**
	 * @param array $data
	 *
	 * @return NotificationStatus
	 */
	private function createStatus( $data ) {
		if ( $data['time'] ) {
			$time = DateTime::createFromFormat( 'YmdHis', $data['time'] );
		} else {
			$time = null;
		}
		return new NotificationStatus( $data['status'], $data['error'], $time );
	}

	/**
	 * @param UserIdentity $user
	 *
	 * @return array
	 */
	private function getUserOutputInfo( UserIdentity $user ): array {
		if ( !( $user instanceof User ) ) {
			$user = $this->userFactory->newFromUserIdentity( $user );
		}
		$realName = $user->getRealName();

		return [
			'display_name' => $realName ?: $user->getName(),
			'username' => $user->getName(),
			'user_page' => $user->getUserPage()->getFullURL(),
		];
	}

	/**
	 * @param string $message
	 * @return array
	 */
	private function prepareMessageForWeb( string $message ): array {
		// Split on \n or <br>
		$lines = preg_split( '/\n|<br\s*\/?>/', $message );
		// Remove empty lines
		$lines = array_filter( $lines, static function ( $line ) {
			return trim( $line ) !== '';
		} );
		$firstLine = array_shift( $lines );
		$rest = implode( '<br>', $lines );
		return [ 'main' => $firstLine, 'secondary' => $rest ];
	}

	/**
	 * @param Notification[] $notifications
	 * @return Notification
	 */
	private function getMostRecentNotification( array $notifications ): Notification {
		$mostRecent = null;
		$mostRecentTime = null;
		foreach ( $notifications as $notification ) {
			$time = $notification->getEvent()->getTime();
			if ( $mostRecentTime === null || $time > $mostRecentTime ) {
				$mostRecent = $notification;
				$mostRecentTime = $time;
			}
		}
		return $mostRecent;
	}
}
