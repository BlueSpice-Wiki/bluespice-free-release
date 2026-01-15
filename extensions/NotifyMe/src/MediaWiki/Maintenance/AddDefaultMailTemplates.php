<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\Maintenance;

use Exception;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Content\JsonContent;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\NotifyMe\MediaWiki\Content\MailTemplate;
use MediaWiki\Maintenance\LoggedUpdateMaintenance;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\User\User;

require_once __DIR__ . '/../../../../../maintenance/Maintenance.php';

class AddDefaultMailTemplates extends LoggedUpdateMaintenance {
	/**
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'notifications-default-mail-templates';
	}

	/**
	 * @return bool|void
	 * @throws Exception
	 */
	protected function doDBUpdates() {
		$this->output( "Adding default mail templates...\n" );

		$baseDir = __DIR__ . '/../../Channel/Email/defaultTemplates';
		$templates = [
			'Wrapper.mail' => [
				'file' => $baseDir . '/wrapper.mustache',
				'meta' => [
					'type' => 'wrapper',
					'is_content' => false,
					'sampleData' => [
						'content' =>
							'<p><b><i>' . $this->msg( 'notifyme-sample-data-content' ) . '</i></b></p>'
					],
				]
			],
			'SingleNotification.mail' => [
				'file' => $baseDir . '/content.single.mustache',
				'meta' => [
					'type' => 'single',
					'is_content' => true,
					'sampleData' => $this->getSampleData( 'single' ),
				]
			],
			'DigestNotification.mail' => [
				'file' => $baseDir . '/content.digest.mustache',
				'meta' => [
					'type' => 'digest',
					'is_content' => true,
					'sampleData' => $this->getSampleData( 'digest' ),
				]
			]
		];

		// No injection possible :(
		$services = MediaWikiServices::getInstance();
		$titleFactory = $services->getTitleFactory();
		$wikiPageFactory = $services->getWikiPageFactory();
		foreach ( $templates as $page => $data ) {
			if ( !file_exists( $data['file'] ) ) {
				$this->output( "... File {$data['file']} does not exist, skipping.\n" );
				continue;
			}
			$title = $titleFactory->newFromText( $page, NS_MEDIAWIKI );
			if ( $title->exists() && !$this->hasOption( 'force' ) ) {
				$this->output( "... Page $page already exists, skipping.\n" );
				continue;
			}

			$this->output( "... Adding $page..." );
			$content = file_get_contents( $data['file'] );
			$wikiPage = $wikiPageFactory->newFromTitle( $title );
			$updater = $wikiPage->newPageUpdater( $this->getMaintenanceUser() );
			$content = new MailTemplate( $content );
			$updater->setContent( 'main', $content );
			$meta = new JsonContent( json_encode( $data['meta'] ) );
			$updater->setContent( 'mail_template_meta', $meta );
			$rev = $updater->saveRevision(
				CommentStoreComment::newUnsavedComment( 'Default mail template content' )
			);
			if ( $rev instanceof RevisionRecord ) {
				$this->output( "done\n" );
			} else {
				$this->output( "failed. {$updater->getStatus()->getMessage()->text()}\n" );
			}
		}

		return true;
	}

	/**
	 * @return User
	 */
	private function getMaintenanceUser(): User {
		return User::newSystemUser( 'MediaWiki default', [ 'steal' => true ] );
	}

	/**
	 * @param string $type
	 *
	 * @return array
	 * @throws Exception
	 */
	private function getSampleData( string $type ) {
		$sampleData = [
			'agent' => [
				'username' => 'agent123',
				'display_name' => $this->msg( 'notifyme-sample-data-agent' ),
				'userpage' => '#'
			],
			'message' => $this->msg( 'notifyme-sample-data-message' ),
			'target_user' => [
				'username' => 'target123',
				'display_name' => $this->msg( 'notifyme-sample-data-target' ),
				'userpage' => '#'
			],
			'links_intro' => $this->msg( 'notifyme-sample-data-links-intro' ),
			'timestamp' => RequestContext::getMain()->getLanguage()->timeanddate( 20220101101010 ),
			'links' => [
				[
					'primary' => true, 'url' => '#', 'label' => $this->msg( 'notifyme-sample-data-link-1' ),
				],
				[
					'url' => '#', 'label' => $this->msg( 'notifyme-sample-data-link-2' ),
				]
			],
			'source_providers' => [
				[
					'link' => '#',
					'description' => $this->msg( 'notifyme-sample-data-source-provider-1' ),
				],
				[
					'link' => '#',
					'description' => $this->msg( 'notifyme-sample-data-source-provider-2' ),
				]
			]
		];

		if ( $type === 'digest' ) {
			return [
				'target_user' => [
					'username' => 'target123',
					'display_name' => $this->msg( 'notifyme-sample-data-target' ),
					'userpage' => '#'
				],
				'notifications' => [
					[
						'type_group' => true,
						'message' => $this->msg( 'notifyme-sample-data-message-group' ),
						'count' => 3,
						'link' => '#',
					],
					[
						'type_single' => true,
						'agent' => $sampleData['agent'],
						'message' => $sampleData['message'],
						'link' => '#',
					]
				],
				'subscription_center_link' => '#'
			];
		}

		return $sampleData;
	}

	/**
	 * @param string $key
	 *
	 * @return string
	 */
	private function msg( $key ) {
		return "{{int:$key}}";
	}
}

$maintClass = AddDefaultMailTemplates::class;
require_once RUN_MAINTENANCE_IF_MAIN;
