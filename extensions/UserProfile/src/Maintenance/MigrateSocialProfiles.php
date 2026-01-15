<?php

namespace MediaWiki\Extension\UserProfile\Maintenance;

use MediaWiki\Extension\UserProfile\ProfileManager;
use MediaWiki\Maintenance\LoggedUpdateMaintenance;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\User\User;
use Throwable;

require_once dirname( __DIR__, 4 ) . '/maintenance/Maintenance.php';

class MigrateSocialProfiles extends LoggedUpdateMaintenance {

	/** @var array|string[] */
	private array $fieldMapping = [
		'social-profile-department' => 'department',
		'social-profile-phone' => 'phone',
		'social-profile-location' => 'location',
		'social-profile-function' => 'function',
	];

	/** @var array|string[] */
	private array $typeMapping = [
		'string' => 'text',
		'boolean' => 'checkbox',
		'integer' => 'number',
		'select' => 'dropdown',
	];

	/**
	 * @return true
	 */
	protected function doDBUpdates() {
		// Migrate user pref
		$this->output( "Migrating user preferences..." );
		$db = $this->getDB( DB_PRIMARY );
		$db->update(
			'user_properties',
			[
				'up_property' => 'user-profile-mail-public',
			],
			[
				'up_property' => 'social-profile-infoshowemail'
			],
			__METHOD__
		);
		$this->output( "done\n" );

		if ( !$this->hasSocialPages() ) {
			$this->output( "No BlueSpiceSocial content found. Skipping profile data migration.\n" );
			return true;
		}
		$this->output( "Searching for SocialProfilePages pages...\n" );
		$profilePages = $this->getSocialProfilePages();
		$this->output( "Found " . count( $profilePages ) . " profile pages.\n" );
		$count = 0;
		foreach ( $profilePages as $data ) {
			if ( $count > 0 && $count % 100 === 0 ) {
				$this->output( "Migrated $count pages...\n" );
			}
			if ( $this->migratePage( $data['user'], $data['data'] ) ) {
				$count++;
			}

		}
		$this->output( "Migrated $count pages.\n" );
		return true;
	}

	/**
	 * @return bool
	 */
	private function hasSocialPages(): bool {
		return $this->getDB( DB_REPLICA )->selectRowCount(
			'page',
			'*',
			[
				'page_namespace' => 1506,
			],
			__METHOD__
		) > 0;
	}

	/**
	 * @return array
	 */
	private function getSocialProfilePages(): array {
		$pages = $this->getDB( DB_REPLICA )->select(
			'page',
			[ 'page_title', 'page_id', 'page_namespace' ],
			[
				'page_namespace' => 1506,
			],
		);
		$customFields = $this->getCustomFields();
		$data = [];
		foreach ( $pages as $page ) {
			$title = $this->getServiceContainer()->getTitleFactory()->newFromRow( $page );
			$revision = $this->getServiceContainer()->getRevisionStore()->getRevisionByTitle( $title );
			if ( !$revision ) {
				continue;
			}
			$content = $revision->getContent( SlotRecord::MAIN );
			if ( !$content ) {
				continue;
			}
			$text = $content->getNativeData();
			$json = json_decode( $text, true );
			if ( !$json ) {
				continue;
			}
			if ( !isset( $json['type'] ) || $json['type'] !== 'profile' ) {
				continue;
			}
			if ( $json['archived'] ) {
				continue;
			}
			$user = $this->getServiceContainer()->getUserFactory()->newFromId( $json['ownerid'] );
			if ( !$user || !$user->isRegistered() ) {
				continue;
			}

			$newProfileData = [];
			foreach ( $this->fieldMapping as $oldKey => $newKey ) {
				if ( isset( $json[$oldKey] ) && $json[$oldKey] ) {
					$newProfileData[$newKey] = $json[$oldKey];
				}
			}
			foreach ( $customFields as $key => $def ) {
				if ( isset( $json[$key] ) && $json[$key] ) {
					$newProfileData[$key] = $json[$key];
				}
			}
			if ( $newProfileData ) {
				$data[$user->getId()] = [
					'user' => $user,
					'data' => $newProfileData,
				];
			}
		}

		return $data;
	}

	/**
	 * @param User $user
	 * @param array $data
	 * @return bool
	 */
	private function migratePage( User $user, array $data ): bool {
		$this->output( "Migrating profile for {$user->getName()}..." );
		/** @var ProfileManager $manager */
		$manager = $this->getServiceContainer()->getService( 'UserProfile.Manager' );
		try {
			$manager->setProfileData(
				$data, $user, User::newSystemUser( 'MediaWiki default', [ 'steal' => true ] )
			);
			$this->output( "done\n" );
		} catch ( Throwable $ex ) {
			$this->error( $ex->getMessage() . "\n" );
			return false;
		}
		return true;
	}

	/**
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'migrate-social-profiles-from-pages';
	}

	/**
	 * @return array
	 */
	private function getCustomFields(): array {
		$raw = $GLOBALS['bsgBSSocialProfileCustomFields'] ?? [];
		$customFields = [];
		foreach ( $raw as $key => $value ) {
			if ( isset( $this->fieldMapping[$key] ) ) {
				continue;
			}
			$type = $this->typeMapping[$value['type']] ?? 'text';
			$formDefinition = [ 'type' => $type ];
			if ( $type === 'dropdown' ) {
				$formDefinition['options'] = $value['options'] ?? [];
			}
			$customFields[$key] = [
				'msgKey' => $value['i18n'],
				'formDefinition' => $formDefinition,
			];
			$GLOBALS['wgUserProfileFields'][$key] = $customFields[$key];
		}
		return $customFields;
	}
}

$maintClass = MigrateSocialProfiles::class;
require_once RUN_MAINTENANCE_IF_MAIN;
