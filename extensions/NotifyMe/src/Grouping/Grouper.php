<?php

namespace MediaWiki\Extension\NotifyMe\Grouping;

use MWStake\MediaWiki\Component\Events\GroupableEvent;
use MWStake\MediaWiki\Component\Events\ITitleEvent;
use MWStake\MediaWiki\Component\Events\Notification;

final class Grouper {
	/** @var array */
	private $keys = [];
	/**
	 * @var array
	 */
	private $notifications;

	/**
	 * @param array $notifications
	 */
	public function __construct( array $notifications ) {
		$this->notifications = $notifications;
	}

	/**
	 * Group notifications by state of delivery
	 * @return $this
	 */
	public function onStatus() {
		$this->keys[] = 'status';
		return $this;
	}

	/**
	 * Group notifications by subject string
	 * @return $this
	 */
	public function onSubject() {
		$this->keys[] = 'subject';
		return $this;
	}

	/**
	 * @return array
	 */
	public function group() {
		$grouped = [];
		/** @var Notification $notification */
		foreach ( $this->notifications as $notification ) {
			$groupKey = $notification->getEvent() instanceof GroupableEvent ?
				$this->calculateGroupingHash( $notification ) :
				$this->calculateGroupingHash( $notification, [
					$notification->getEvent()->getTime()->getTimestamp(),
					rand( 0, 99999 )
				] );
			if ( !isset( $grouped[$groupKey] ) ) {
				$grouped[$groupKey] = [];
			}
			$grouped[$groupKey][] = $notification;
		}

		$result = [];
		foreach ( $grouped as $group ) {
			if ( empty( $group ) ) {
				continue;
			}
			if ( count( $group ) === 1 ) {
				$result[] = $group[0];
				continue;
			}
			$toGroup = [];
			foreach ( $group as $notification ) {
				if ( $notification->getEvent() instanceof GroupableEvent ) {
					$toGroup[] = $notification;
				} else {
					$result[] = $notification;
				}
			}
			$result[] = new NotificationGroup( $this->sortByTimeDescending( $toGroup ) );
		}

		$this->keys = [];
		return $this->sortByTimeDescending( $result );
	}

	/**
	 * @param Notification $notification
	 * @param array $additionalParts
	 * @return string
	 */
	private function calculateGroupingHash( Notification $notification, array $additionalParts = [] ) {
		$hash = [];
		foreach ( $this->keys as $key ) {
			$hash[] = $this->getHashPart( $notification, $key );
		}
		$hash[] = $notification->getEvent()->getKey();
		if ( $notification->getEvent() instanceof ITitleEvent ) {
			$hash[] = $notification->getEvent()->getTitle()->getPrefixedDBkey();
		}
		$hash = array_merge( $hash, $additionalParts );

		return md5( implode( '-', $hash ) );
	}

	/**
	 * @param Notification $notification
	 * @param string $key
	 *
	 * @return mixed|string
	 */
	private function getHashPart( $notification, $key ) {
		switch ( $key ) {
			case 'status':
				return $notification->getStatus()->getStatus();
			case 'agent':
				return $notification->getEvent()->getAgent()->getId();
			case 'subject':
				return $notification->getEvent()->getMessage( $notification->getChannel() )->plain();
		}

		return '';
	}

	/**
	 * Sort notifications in the group by time
	 * @param array $group
	 *
	 * @return array
	 */
	private function sortByTimeDescending( array $group ): array {
		usort( $group, static function ( $a, $b ) {
			// Sort by time descending
			return $b->getEvent()->getTime()->getTimestamp() - $a->getEvent()->getTime()->getTimestamp();
		} );

		return $group;
	}
}
