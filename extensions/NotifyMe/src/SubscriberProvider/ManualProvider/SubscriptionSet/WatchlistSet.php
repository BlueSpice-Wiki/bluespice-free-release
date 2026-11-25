<?php

namespace MediaWiki\Extension\NotifyMe\SubscriberProvider\ManualProvider\SubscriptionSet;

use MediaWiki\Extension\NotifyMe\SubscriberProvider\ManualProvider\ISubscriptionSet;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\INotificationEvent;
use MWStake\MediaWiki\Component\Events\ITitleEvent;
use Wikimedia\Rdbms\ILoadBalancer;

class WatchlistSet implements ISubscriptionSet {

	/** @var array */
	private array $watchers = [];

	/**
	 * @param ILoadBalancer $lb
	 */
	public function __construct( private readonly ILoadBalancer $lb ) {
	}

	/**
	 * @inheritDoc
	 */
	public function isSubscribed( array $setData, INotificationEvent $event, UserIdentity $user ): bool {
		if ( !( $event instanceof ITitleEvent ) ) {
			return false;
		}
		$watchers = $this->getWatchers( $event->getTitle() );
		return in_array( $user->getId(), $watchers );
	}

	/**
	 * @inheritDoc
	 */
	public function getClientSideModule(): string {
		return 'ext.notifyme.subscription.set';
	}

	/**
	 * @param Title $title
	 * @return array
	 */
	private function getWatchers( Title $title ): array {
		if ( isset( $this->watchers[$title->getArticleID()] ) ) {
			return $this->watchers[$title->getArticleID()];
		}

		$db = $this->lb->getConnection( DB_REPLICA );
		$res = $db->newSelectQueryBuilder()
			->from( 'watchlist', 'wa' )
			->from( 'watchlist_expiry', 'we' )
			->select( 'wa.wl_user' )
			->select( 'we.we_expiry' )
			->where( [
				'wa.wl_title' => $title->getDbKey(),
				'wa.wl_namespace' => $title->getNamespace(),
			] )
			->leftJoin( 'watchlist_expiry', 'we', [ 'wa.wl_id = we.we_item' ] )
			->caller( __METHOD__ )
			->fetchResultSet();

		$this->watchers[$title->getArticleID()] = [];
		foreach ( $res as $row ) {
			$expiry = $row->we_expiry;
			if ( $expiry !== null ) {
				$expiry = wfTimestamp( TS_UNIX, $expiry );
				if ( $expiry < time() ) {
					continue;
				}
			}
			$this->watchers[$title->getArticleID()][] = $row->wl_user;

		}
		return $this->watchers[$title->getArticleID()];
	}
}
