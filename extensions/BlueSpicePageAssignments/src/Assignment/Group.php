<?php
namespace BlueSpice\PageAssignments\Assignment;

use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Title\Title;

class Group extends \BlueSpice\PageAssignments\Assignment {

	/**
	 *
	 * @var int[]
	 */
	protected static $userIdCache = [];

	/**
	 *
	 * @return string
	 */
	protected function makeAnchor() {
		return $this->linkRenderer->makeLink(
			Title::makeTitle( NS_PROJECT, $this->getText() ),
			new \HtmlArmor( $this->getText() )
		);
	}

	/**
	 *
	 * @return string
	 */
	public function getText() {
		return Message::newFromKey( "group-{$this->getKey()}" )->exists()
			? Message::newFromKey( "group-{$this->getKey()}" )->text()
			: $this->getKey();
	}

	/**
	 *
	 * @return int[]
	 */
	public function getUserIds() {
		if ( isset( static::$userIdCache[$this->getKey()] ) ) {
			return static::$userIdCache[$this->getKey()];
		}
		static::$userIdCache[$this->getKey()] = [];

		$services = MediaWikiServices::getInstance();
		$loadBalancer = $services->getDBLoadBalancer();
		$res = $loadBalancer->getConnection( DB_REPLICA )->select(
			'user_groups',
			'ug_user',
			[
				'ug_group' => $this->getKey()
			],
			__METHOD__
		);
		$pm = $services->getPermissionManager();
		$userFactory = $services->getUserFactory();
		$title = $this->getTitle();
		foreach ( $res as $row ) {
			$allowed = $pm->userCan(
				'pageassignable',
				$userFactory->newFromId( (int)$row->ug_user ),
				$title
			);
			if ( !$allowed ) {
				continue;
			}
			static::$userIdCache[$this->getKey()][] = (int)$row->ug_user;
		}

		return static::$userIdCache[$this->getKey()];
	}

}
