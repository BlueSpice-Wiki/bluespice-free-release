<?php

use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\Events\BotAgent;

require_once __DIR__ . '/../../../maintenance/Maintenance.php';

class TriggerEvent extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Send out a test notification to a particular set of users' );

		$this->addOption( 'key', 'Notification key', false, true );
		$this->addOption( 'agent', 'Username of the agent', false, true );
		$this->addOption( 'page', 'Page that is subject of event (for TitleEvents only)', false, true );
		$this->addOption(
			'target-user', 'User to be targeted by the notification (only for events that expect it)', false, true
		);
		$this->addOption( 'trigger-all', 'Trigger all events', false, false );
	}

	/**
	 * Trigger specified notification event
	 *
	 * @return void
	 * @throws Exception If any required parameter is invalid or missing.
	 */
	public function execute() {
		$services = MediaWikiServices::getInstance();
		/** @var \MediaWiki\Extension\NotifyMe\NotificationTester $tester */
		$tester = $services->getService( 'NotifyMe.Tester' );
		$titleFactory = $services->getTitleFactory();
		$userFactory = $services->getUserFactory();

		$specs = $tester->getEventSpecs();
		if ( !$this->hasOption( 'key' ) && !$this->hasOption( 'trigger-all' ) ) {
			$this->output( "Available event keys (and buckets):\n" );
			foreach ( $specs as $eventKey => $spec ) {
				$buckets = $spec['buckets'] ?? [];
				$this->output( "> $eventKey" . ( $buckets ? ' (' . implode( ', ', $buckets ) . ')' : '' ) . "\n" );
			}

			$this->output(
				"\nTo trigger an event, specify one of the keys using the --key parameter, or specify --trigger-all\n"
			);
			return;
		}

		if ( $this->getOption( 'agent' ) === 'bot' ) {
			$agent = new BotAgent();
		} else {
			$agent = $userFactory->newFromName( $this->getOption( 'agent' ) );
		}
		if ( !$agent || ( !$agent->isRegistered() && !( $agent instanceof BotAgent ) ) ) {
			throw new Exception( "Invalid agent\n" );
		}

		$targetUser = null;
		if ( $this->hasOption( 'target-user' ) ) {
			$targetUserName = $this->getOption( 'target-user', 'WikiSysop' );
			$targetUser = $userFactory->newFromName( $targetUserName );
			if ( !$targetUser || !$targetUser->isRegistered() ) {
				throw new Exception( "Invalid target-user\n" );
			}
		}
		$page = $titleFactory->newMainPage();
		if ( $this->hasOption( 'page' ) ) {
			$page = $titleFactory->newFromText( $this->getOption( 'page' ) );
			if ( !$page ) {
				throw new Exception( "Invalid page\n" );
			}
		}

		if ( $this->hasOption( 'trigger-all' ) ) {
			foreach ( $specs as $eventKey => $spec ) {
				$this->output( "Triggering: $eventKey\n" );

				try {
					$tester->triggerForKey( $eventKey, $agent, $page, $targetUser );
				} catch ( Exception $e ) {
					$this->error( $e->getMessage() );
				}
			}
		} else {
			try {
				$key = $this->getOption( 'key', '' );
				$this->output( "Triggering: $key\n" );

				$tester->triggerForKey( $key, $agent, $page, $targetUser );
			} catch ( Exception $e ) {
				$this->error( $e->getMessage() );

				return;
			}
		}

		$this->output( "Sent\n\n" );
		$this->output( "Worked? Cool!\n" );
		$this->output( "Did not work? Try these debugging steps:\n" );
		$this->output( "1. Is `processmanager` running? Check its logs on channel `ProcessRunner`. \n" );
		$this->output( "2. Check the logs for errors. PHP error_log and on channel `notifications` \n" );
		$this->output(
			"3. Did you get notification in the web, but not on email? " .
			"Check if the users(s) have confirmed email address. " .
			"Wiki configuration ok? \$wgPasswordSender, \$wgEnableEmail... \n"
		);
		$this->output( "4. Check user's preferences, " .
			"is user you are expecting to get the notification subscribed to its bucket?. \n" );
		$this->output( "5. Do parameters passed to the script fit the notification being triggered? " .
			"eg. are you trying to trigger discussion edit on a non NS_USER_TALK page. \n" );
	}
}

$maintClass = TriggerEvent::class;
require_once RUN_MAINTENANCE_IF_MAIN;
