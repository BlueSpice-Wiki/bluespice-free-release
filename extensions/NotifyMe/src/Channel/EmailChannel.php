<?php

namespace MediaWiki\Extension\NotifyMe\Channel;

use Exception;
use MailAddress;
use MediaWiki\Config\Config;
use MediaWiki\Extension\NotifyMe\Channel\Email\DigestCreator;
use MediaWiki\Extension\NotifyMe\Channel\Email\MailContentProvider;
use MediaWiki\Extension\NotifyMe\NotificationSerializer;
use MediaWiki\Extension\NotifyMe\Storage\NotificationStore;
use MediaWiki\Extension\NotifyMe\SubscriptionConfigurator;
use MediaWiki\Language\Language;
use MediaWiki\MainConfigNames;
use MediaWiki\Message\Message;
use MediaWiki\User\Options\UserOptionsLookup;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use MWException;
use MWStake\MediaWiki\Component\Events\Delivery\IExternalChannel;
use MWStake\MediaWiki\Component\Events\INotificationEvent;
use MWStake\MediaWiki\Component\Events\ITitleEvent;
use MWStake\MediaWiki\Component\Events\Notification;
use MWStake\MediaWiki\Component\Events\PriorityEvent;
use Psr\Log\LoggerInterface;
use UserMailer;

class EmailChannel implements IExternalChannel {
	/** @var NotificationStore */
	private $store;
	/** @var NotificationSerializer */
	private $serializer;
	/**
	 * @var SubscriptionConfigurator
	 */
	private $subscriptionConfigurator;
	/**
	 * @var LoggerInterface
	 */
	private $logger;
	/**
	 * @var MailContentProvider
	 */
	private $mailContentProvider;

	/**
	 * @var Config
	 */
	private $config;

	/** @var UserOptionsLookup */
	private $userOptionsLookup;

	/** @var Language */
	private $language;

	/**
	 * @param NotificationStore $store
	 * @param NotificationSerializer $serializer
	 * @param MailContentProvider $mailContentProvider
	 * @param SubscriptionConfigurator $configurator
	 * @param LoggerInterface $logger
	 * @param Config $mainConfig
	 * @param UserOptionsLookup $userOptionsLookup
	 * @param Language $language
	 */
	public function __construct(
		 NotificationStore $store, NotificationSerializer $serializer, MailContentProvider $mailContentProvider,
		 SubscriptionConfigurator $configurator, LoggerInterface $logger, Config $mainConfig,
		 UserOptionsLookup $userOptionsLookup, Language $language
	) {
		$this->store = $store;
		$this->serializer = $serializer;
		$this->subscriptionConfigurator = $configurator;
		$this->logger = $logger;
		$this->mailContentProvider = $mailContentProvider;
		$this->config = $mainConfig;
		$this->userOptionsLookup = $userOptionsLookup;
		$this->language = $language;
	}

	/**
	 * @return Message
	 */
	public function getLabel(): Message {
		return Message::newFromKey( 'notifyme-channel-email' );
	}

	/**
	 * @param Notification $notification
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function deliver( Notification $notification ): bool {
		$user = $notification->getTargetUser();
		if ( !$this->canReceiveMail( $user ) ) {
			throw new MWException( 'User ' . $user->getName() . ' cannot receive mail' );
		}
		if (
			!$this->userWantsImmediateEmail( $user ) &&
			!( $notification->getEvent() instanceof PriorityEvent )
		) {
			return false;
		}

		$mail = $this->getSingleMail( $notification, $user );
		$this->send( $mail, $user );
		return true;
	}

	/**
	 * @param User $user
	 * @param array $notifications
	 * @param string $digestType
	 *
	 * @return void|null
	 * @throws MWException
	 */
	public function digest( User $user, array $notifications, string $digestType ) {
		if ( !$this->canReceiveMail( $user ) ) {
			$this->logger->error( 'User {name} cannot receive mail', [
				'name' => $user->getName()
			] );
			return;
		}
		$digestCreator = new DigestCreator( $this->serializer, $this->mailContentProvider, $this->config );
		$mail = $digestCreator->createDigest( $user, $notifications, $digestType );
		if ( !$mail ) {
			return;
		}

		$this->send( $mail, $user );

		/** @var Notification $notification */
		foreach ( $notifications as $notification ) {
			// Mark all notifications as delivered
			$notification->getStatus()->markAsCompleted();
			$this->store->persist( $notification );
		}
	}

	/**
	 * @param INotificationEvent $event
	 * @param UserIdentity $user
	 *
	 * @return bool
	 */
	public function shouldSkip( INotificationEvent $event, UserIdentity $user ): bool {
		return false;
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'email';
	}

	/**
	 * @return string[]
	 */
	public function getDefaultConfiguration(): array {
		return [
			'frequency' => 'daily',
		];
	}

	/**
	 * @param User $user
	 *
	 * @return bool
	 */
	private function canReceiveMail( User $user ) {
		return $user->getEmail() && $user->isEmailConfirmed();
	}

	/**
	 * @param Notification $notification
	 * @param User $user
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function getSingleMail( Notification $notification, User $user ) {
		return [
			'subject' => $this->generateSubject( $notification->getEvent(), $user ),
			'body' => $this->mailContentProvider->getFinalEmailHtml(
				'single',
				$this->serializer->serializeForOutput( $notification, $user ),
				$user
			),
			'options' => [
				'contentType' => 'text/html;charset=UTF-8'
			],
		];
	}

	/**
	 * @param User $user
	 *
	 * @return bool
	 */
	private function userWantsImmediateEmail( User $user ) {
		return $this->getFrequencyPreference( $user ) === 'instant';
	}

	/**
	 * @param UserIdentity $user
	 *
	 * @return string
	 */
	public function getFrequencyPreference( UserIdentity $user ) {
		$channelConf = $this->subscriptionConfigurator->getChannelConfiguration( $user, $this );
		return $channelConf['frequency'] ?? 'instant';
	}

	/**
	 * @param array $mail
	 * @param User $user
	 *
	 * @return void
	 * @throws MWException
	 */
	private function send( array $mail, User $user ) {
		$from = new MailAddress(
			$this->config->get( MainConfigNames::PasswordSender ),
			Message::newFromKey( 'emailsender' )->text()
		);
		$sendTo = MailAddress::newFromUser( $user );
		$status = UserMailer::send( $sendTo, $from, $mail['subject'], $mail['body'], $mail['options'] );
		if ( !$status->isOK() ) {
			throw new Exception( $status->getWikiText() );
		}
	}

	/**
	 * @param INotificationEvent $event
	 * @param User $user
	 *
	 * @return string
	 */
	private function generateSubject( INotificationEvent $event, User $user ): string {
		$userLanguage = $this->userOptionsLookup->getOption( $user, 'language' );
		if ( !$userLanguage ) {
			$userLanguage = $this->language;
		}
		$typeMessage = $event->getKeyMessage()->inLanguage( $userLanguage )->text();

		if ( $event instanceof ITitleEvent ) {
			return $typeMessage . ' - ' . $event->getTitle()->getPrefixedText();
		}
		return $typeMessage;
	}

	/**
	 * @inheritDoc
	 */
	public function onNotificationPersisted( Notification $notification, bool $created ): void {
		// No-op
	}

	/**
	 * @inheritDoc
	 */
	public function onNotificationOutputSerialized( Notification $notification, array &$data ): void {
		// No-op
	}
}
