<?php

namespace MediaWiki\Extension\NotifyMe;

use Exception;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\Notifier;

class NotificationTester {

	/** @var EventFactory */
	private $eventFactory;

	/** @var MediaWikiServices */
	private $services;

	/** @var Notifier */
	private $notifier;

	/**
	 * @param EventFactory $eventFactory
	 * @param MediaWikiServices $services
	 * @param Notifier $notifier
	 */
	public function __construct( EventFactory $eventFactory, MediaWikiServices $services, Notifier $notifier ) {
		$this->eventFactory = $eventFactory;
		$this->services = $services;
		$this->notifier = $notifier;
	}

	/**
	 * Trigger event for the specified key
	 *
	 * @param string $key
	 * @param UserIdentity $agent
	 * @param Title|null $page
	 * @param UserIdentity|null $targetUser
	 * @return void
	 * @throws Exception If any required parameter is invalid or missing.
	 */
	public function triggerForKey(
		string $key, UserIdentity $agent, ?Title $page = null, ?UserIdentity $targetUser = null
	) {
		if ( !$key || !$this->eventFactory->hasEvent( $key ) ) {
			throw new Exception( "Invalid key\n" );
		}

		$args = [];
		if ( $page ) {
			$args['title'] = $page;
		}
		if ( $targetUser ) {
			$args['targetUser'] = $targetUser;
		}

		$event = $this->eventFactory->getTestEvent( $key, $agent, $this->services, $args );
		$this->notifier->emit( $event );
	}

	/**
	 * @return array
	 */
	public function getEventSpecs(): array {
		return $this->eventFactory->getEventSpecs();
	}
}
