<?php

namespace BlueSpice\PageAssignments\Notifications\SubscriberProviderSet;

use BlueSpice\PageAssignments\AssignmentFactory;
use MediaWiki\Extension\NotifyMe\SubscriberProvider\ManualProvider\ISubscriptionSet;
use MediaWiki\User\UserGroupManager;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\INotificationEvent;
use MWStake\MediaWiki\Component\Events\ITitleEvent;
use Wikimedia\Rdbms\ILoadBalancer;

class Assigned implements ISubscriptionSet {

	/**
	 * @var ILoadBalancer
	 */
	protected $lb;

	/**
	 * @var AssignmentFactory
	 */
	protected $assignmentFactory;

	/**
	 * @var UserGroupManager
	 */
	protected $userGroupManager;

	/**
	 * @param ILoadBalancer $lb
	 * @param AssignmentFactory $assignmentFactory
	 * @param UserGroupManager $userGroupManager
	 */
	public function __construct(
		ILoadBalancer $lb, AssignmentFactory $assignmentFactory, UserGroupManager $userGroupManager
	) {
		$this->lb = $lb;
		$this->assignmentFactory = $assignmentFactory;
		$this->userGroupManager = $userGroupManager;
	}

	/**
	 * @inheritDoc
	 */
	public function isSubscribed( array $setData, INotificationEvent $event, UserIdentity $user ): bool {
		if ( !( $event instanceof ITitleEvent ) ) {
			return false;
		}
		$assignedPages = $this->getAssignments( $user );
		return in_array( $event->getTitle()->getArticleID(), $assignedPages );
	}

	/**
	 * @inheritDoc
	 */
	public function getClientSideModule(): string {
		return 'ext.bluespice.pageassignments.notifications.subscriptionSet';
	}

	/**
	 * @param UserIdentity $user
	 * @return array
	 */
	private function getAssignments( UserIdentity $user ): array {
		$db = $this->lb->getConnection( DB_REPLICA );
		$registeredTypes = $this->assignmentFactory->getRegisteredTypes();
		$result = $this->doGetAssignments( [
			'pa.pa_assignee_type IN (' . $db->makeList( $registeredTypes ) . ')',
			'pa.pa_assignee_key' => $user->getName(),
		] );
		if ( in_array( 'group', $registeredTypes ) ) {
			$userGroups = $this->userGroupManager->getUserGroups( $user );
			if ( !empty( $userGroups ) ) {
				$result += $this->doGetAssignments( [
					'pa.pa_assignee_type' => 'group',
					'pa.pa_assignee_key IN (' . $db->makeList( $userGroups ) . ')'
				] );
			}
		}

		if ( in_array( 'everyone', $registeredTypes ) ) {
			$result += $this->doGetAssignments( [
				'pa.pa_assignee_type' => 'everyone',
			] );
		}

		return array_unique( $result );
	}

	/**
	 * @param array $conds
	 * @return array
	 */
	protected function doGetAssignments( array $conds ): array {
		$db = $this->lb->getConnection( DB_REPLICA );
		$res = $db->select(
			[ 'pa' => 'bs_pageassignments', 'p' => 'page' ],
			[ 'page_id' ],
			$conds,
			__METHOD__,
			[],
			[
				// INNER JOIN to ensure that page exists
				'p' => [ 'INNER JOIN', [ 'p.page_id = pa.pa_page_id' ] ]
			]
		);

		$assignedPages = [];
		foreach ( $res as $row ) {
			$assignedPages[] = (int)$row->page_id;
		}

		return $assignedPages;
	}

}
