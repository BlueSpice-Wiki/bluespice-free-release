<?php

namespace MediaWiki\Extension\NotifyMe\SubscriberProvider\ManualProvider\SubscriptionSet;

use MediaWiki\Extension\NotifyMe\SubscriberProvider\ManualProvider\ISubscriptionSet;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\INotificationEvent;
use MWStake\MediaWiki\Component\Events\ITitleEvent;

class CategorySet implements ISubscriptionSet {

	/**
	 * @var TitleFactory
	 */
	private $titleFactory;

	/**
	 * @param TitleFactory $titleFactory
	 */
	public function __construct( TitleFactory $titleFactory ) {
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function isSubscribed( array $setData, INotificationEvent $event, UserIdentity $user ): bool {
		if ( !( $event instanceof ITitleEvent ) ) {
			return false;
		}
		$category = is_array( $setData['category'] ) ? $setData['category'][0] : $setData['category'];
		if ( !$category ) {
			return false;
		}
		$categoryTitle = $this->titleFactory->newFromText( $category, NS_CATEGORY );
		if ( !$categoryTitle ) {
			return false;
		}
		if ( $event->getTitle()->equals( $categoryTitle ) ) {
			// Also react to changes on category page itself
			return true;
		}
		// Find out if $category matches the category of the event title
		$categories = array_keys( $event->getTitle()->getParentCategories() );
		return in_array( $categoryTitle->getPrefixedDBkey(), $categories );
	}

	/**
	 * @inheritDoc
	 */
	public function getClientSideModule(): string {
		return 'ext.notifyme.subscription.set';
	}
}
