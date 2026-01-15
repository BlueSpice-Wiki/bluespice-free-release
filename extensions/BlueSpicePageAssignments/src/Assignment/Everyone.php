<?php
namespace BlueSpice\PageAssignments\Assignment;

use BlueSpice\TargetCache\Title\Target;
use BsPageContentProvider;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Title\Title;

class Everyone extends \BlueSpice\PageAssignments\Assignment {

	/**
	 *
	 * @var int[]
	 */
	protected static $userIdCache = null;

	/**
	 *
	 * @return string
	 */
	protected function makeAnchor() {
		return Html::element(
			'span',
			[ 'class' => 'bs-pa-special-everyone' ],
			$this->getText()
		);
	}

	/**
	 *
	 * @return string
	 */
	public function getText() {
		return Message::newFromKey(
			'bs-pageassignments-assignee-special-everyone-label'
		)->text();
	}

	/**
	 *
	 * @return int[]
	 */
	public function getUserIds() {
		if ( isset( static::$userIdCache ) ) {
			return static::$userIdCache;
		}
		static::$userIdCache = [];

		$services = MediaWikiServices::getInstance();
		$loadBalancer = $services->getDBLoadBalancer();
		$res = $loadBalancer->getConnection( DB_REPLICA )->select(
			'user',
			[ 'user_id', 'user_name' ],
			'',
			__METHOD__
		);

		$blacklistedUsers = [];
		$pageName = 'PageAssignments-everyone-blacklist';
		$blacklistedUsersTitle = Title::makeTitle( NS_MEDIAWIKI, $pageName );

		if ( $blacklistedUsersTitle->exists() ) {

			$titleCache = $services->getService( 'BSTargetCacheTitle' );
			$target = new Target( $blacklistedUsersTitle );
			$cacheHandler = $titleCache->getHandler( 'pageassignments-everyone-blacklist', $target );
			$blacklistContent = $cacheHandler->get();
			if ( $blacklistContent === false ) {
				$blacklistContent = trim(
					BsPageContentProvider::getInstance()->getContentFromTitle( $blacklistedUsersTitle )
				);
				$cacheHandler->set( $blacklistContent );
			}
			if ( !empty( $blacklistContent ) ) {
				$blacklistedUsers = explode( "\n", $blacklistContent );
				foreach ( $blacklistedUsers as $k => $v ) {
					$blacklistedUsers[$k] = trim( $v );
				}
			}

		}

		$pm = $services->getPermissionManager();
		$userFactory = $services->getUserFactory();
		$title = $this->getTitle();

		foreach ( $res as $row ) {
			if ( in_array( $row->user_name, $blacklistedUsers ) ) {
				continue;
			}
			$allowed = $pm->userCan(
				'pageassignable',
				$userFactory->newFromId( (int)$row->user_id ),
				$title
			);
			if ( !$allowed ) {
				continue;
			}
			static::$userIdCache[] = (int)$row->user_id;
		}
		return static::$userIdCache;
	}

}
