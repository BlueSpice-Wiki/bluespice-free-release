<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs;

use MediaWiki\User\User;
use Wikimedia\Rdbms\DBConnRef;
use Wikimedia\Rdbms\ILoadBalancer;

class UserIndexUpdater {

	/**
	 * @var ILoadBalancer
	 */
	private $lb = null;

	/**
	 * @param ILoadBalancer $lb
	 */
	public function __construct( ILoadBalancer $lb ) {
		$this->lb = $lb;
	}

	/**
	 * @param User $user
	 *
	 * @return bool
	 */
	public function store( User $user ) {
		/** @var DBConnRef $db */
		$db = $this->lb->getConnection( DB_PRIMARY );
		if ( !$db->tableExists( 'mws_user_index', __METHOD__ ) ) {
			return false;
		}
		$data = [
			'mui_user_id' => $user->getId(),
			'mui_user_name' => mb_strtolower( $user->getName() ),
			'mui_user_real_name' => mb_strtolower( $user->getRealName() )
		];
		return $db->upsert(
			'mws_user_index',
			$data,
			[ 'mui_user_id' ],
			$data,
			__METHOD__
		);
	}

	/**
	 * @param User $user
	 *
	 * @return bool
	 */
	public function delete( User $user ) {
		/** @var DBConnRef $db */
		$db = $this->lb->getConnection( DB_PRIMARY );
		if ( !$db->tableExists( 'mws_user_index', __METHOD__ ) ) {
			return false;
		}

		return $db->delete(
			'mws_user_index',
			[ 'mui_user_id' => $user->getId() ],
			__METHOD__
		);
	}
}
