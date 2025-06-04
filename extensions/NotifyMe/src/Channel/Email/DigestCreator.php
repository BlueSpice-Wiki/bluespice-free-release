<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\NotifyMe\Channel\Email;

use Exception;
use MediaWiki\Config\Config;
use MediaWiki\Extension\NotifyMe\Grouping\Grouper;
use MediaWiki\Extension\NotifyMe\Grouping\NotificationGroup;
use MediaWiki\Extension\NotifyMe\NotificationSerializer;
use MediaWiki\Message\Message;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\User\User;
use MWStake\MediaWiki\Component\Events\Notification;

class DigestCreator {
	public const DIGEST_TYPE_DAILY = 'daily';
	public const DIGEST_TYPE_WEEKLY = 'weekly';

	/** @var NotificationSerializer */
	protected $serializer;
	/**
	 * @var MailContentProvider
	 */
	protected $mailContentProvider;
	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @param NotificationSerializer $serializer
	 * @param MailContentProvider $mailContentProvider
	 * @param Config $config
	 */
	public function __construct(
		NotificationSerializer $serializer, MailContentProvider $mailContentProvider, Config $config
	) {
		$this->serializer = $serializer;
		$this->mailContentProvider = $mailContentProvider;
		$this->config = $config;
	}

	/**
	 * Produce serialized digest data
	 *
	 * @param User $user
	 * @param array $notifications
	 * @param string $type
	 *
	 * @return array|null
	 * @throws Exception
	 */
	public function createDigest( User $user, array $notifications, string $type ): ?array {
		if ( !in_array( $type, [ self::DIGEST_TYPE_DAILY, self::DIGEST_TYPE_WEEKLY ] ) ) {
			throw new Exception( 'Invalid digest type: ' . $type );
		}
		if ( empty( $notifications ) ) {
			return null;
		}
		$grouper = new Grouper( $notifications );
		$grouped = $grouper->onSubject()->group();

		$digestHeader = Message::newFromKey( "notifyme-digest-header-$type" )
			->params( $this->config->get( 'Sitename' ) );
		$content = [
			'digestHeader' => $digestHeader->plain(),
			'target_user' => $user->getRealName() ?: $user->getName(),
			'notifications' => $this->serialize( $grouped, $user ),
			'subscription_center_link' => SpecialPage::getTitleFor(
				'Preferences', false, 'mw-prefsection-notifications'
			)->getFullURL()
		];
		$digestSubject = Message::newFromKey( "notifyme-digest-subject-$type" );

		return [
			'subject' => $digestSubject->text(),
			'body' => $this->mailContentProvider->getFinalEmailHtml( 'digest', $content, $user ),
			'options' => [
				'contentType' => 'text/html;charset=UTF-8'
			],
		];
	}

	/**
	 * @param array $grouped
	 * @param User $user
	 *
	 * @return array
	 * @throws \MWException
	 */
	protected function serialize( array $grouped, User $user ): array {
		$output = [];
		foreach ( $grouped as $group ) {
			if ( $group instanceof Notification ) {
				$output[] = [ 'type_single' => true ] + $this->serializer->serializeForOutput( $group, $user );
				continue;
			}
			if ( $group instanceof NotificationGroup ) {
				$output[] = [ 'type_group' => true, ] +
					$this->serializer->serializeNotificationGroupForOutput( $group, $user );
			}
		}

		return $output;
	}
}
