<?php

namespace BlueSpice\PageAssignments\Data\Assignable\User;

use BlueSpice\PageAssignments\Data\Record;
use MediaWiki\Config\GlobalVarConfig;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use MWStake\MediaWiki\Component\DataStore\Schema;
use Wikimedia\Rdbms\IDatabase;

class PrimaryDataProvider extends \MWStake\MediaWiki\Component\CommonWebAPIs\Data\UserQueryStore\PrimaryDataProvider {

	/**
	 *
	 * @var Title
	 */
	protected $title = null;

	/**
	 * @var array
	 */
	private $groupTestUsers = [];

	/** @var array */
	private $addedUsers = [];

	/**
	 * @param IDatabase $db
	 * @param Schema $schema
	 * @param GlobalVarConfig $mwsgConfig
	 * @param Title $title
	 */
	public function __construct( IDatabase $db, Schema $schema, GlobalVarConfig $mwsgConfig, Title $title ) {
		parent::__construct( $db, $schema, $mwsgConfig );
		$this->title = $title;
	}

	/**
	 * @param ReaderParams $params
	 * @return array
	 */
	protected function makePreFilterConds( ReaderParams $params ) {
		$conds = parent::makePreFilterConds( $params );
		$assignableGroups = $this->getAssignableGroups();
		if ( in_array( 'user', $assignableGroups ) ) {
			// No need to change anything, every user is allowed
			return $conds;
		}
		$conds[] = 'ug_group IN (' . $this->db->makeList( $assignableGroups ) . ')';

		return $conds;
	}

	/**
	 * @inheritDoc
	 */
	protected function getJoinConds( ReaderParams $params ) {
		return parent::getJoinConds( $params ) + [
			'user_groups' => [
				'LEFT OUTER JOIN', [ 'user_id = ug_user' ]
			]
		];
	}

	/**
	 * @return string[]
	 */
	protected function getTableNames() {
		return array_merge( parent::getTableNames(), [ 'user_groups' ] );
	}

	/**
	 *
	 * @param \stdClass $row
	 */
	protected function appendRowToData( $row ) {
		$username = $row->user_name;
		if ( isset( $this->addedUsers[$username] ) ) {
			return;
		}

		if ( $this->isUserBlocked( $row->user_id ) ) {
			return;
		}

		$this->addedUsers[$username] = true;
		$this->data[] = new Record( (object)[
			Record::ASSIGNEE_KEY => $username,
			Record::ASSIGNEE_TYPE => 'user',
			Record::PAGE_ID => $this->title->getArticleID(),
			Record::TEXT => $username
		] );
	}

	/**
	 * @return array
	 */
	private function getAssignableGroups(): array {
		$groups = [];

		// Unfortunately, due to infra of PA extension, cannot inject
		$userFactory = MediaWikiServices::getInstance()->getUserFactory();
		$pm = MediaWikiServices::getInstance()->getPermissionManager();
		$userNoGroup = $this->getUserWithNoGroups();
		if ( $userNoGroup ) {
			// Check if user with no groups can be assigned, if yes, do not check for other groups
			if ( $pm->userCan( 'pageassignable', $userNoGroup, $this->title ) ) {
				$groups[] = 'user';
				return $groups;
			}
		} else {
			// Fallback in case "user with no group" cannot be retrieved
			$config = MediaWikiServices::getInstance()->getMainConfig();
			$groupPermissions = $config->get( 'GroupPermissions' );
			if ( isset( $groupPermissions['user']['pageassignable'] ) ) {
				$groups[] = 'user';
				return $groups;
			}
		}

		// If not every user can be assigned, check which groups can
		$res = $this->db->select(
			'user_groups',
			[ 'DISTINCT( ug_group ) user_group', 'MIN( ug_user ) user' ],
			[],
			__METHOD__,
			[ 'GROUP BY' => 'ug_group' ]
		);

		foreach ( $res as $row ) {
			$group = $row->user_group;
			$user = $row->user;
			if ( !isset( $this->groupTestUsers[$group] ) ) {
				$userObject = $userFactory->newFromId( $user );
				$this->groupTestUsers[$group] = $userObject;
			}
			$userObject = $this->groupTestUsers[$group];
			if ( !$userObject ) {
				continue;
			}
			$allowed = $pm->userCan( 'pageassignable', $userObject, $this->title );
			if ( !$allowed ) {
				continue;
			}
			$groups[] = $group;
		}

		return $groups;
	}

	/**
	 * @return User|null
	 */
	private function getUserWithNoGroups(): ?User {
		$user = User::newSystemUser( 'BlueSpice default', [ 'steal' => true ] );
		if ( !$user ) {
			return null;
		}
		$ugm = MediaWikiServices::getInstance()->getUserGroupManager();
		foreach ( $ugm->getUserGroups( $user ) as $group ) {
			$ugm->removeUserFromGroup( $user, $group );
		}

		return $user;
	}

}
