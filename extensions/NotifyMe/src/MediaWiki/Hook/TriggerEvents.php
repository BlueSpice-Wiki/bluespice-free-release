<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\Hook;

use Exception;
use ManualLogEntry;
use MediaWiki\Deferred\LinksUpdate\LinksTable;
use MediaWiki\Deferred\LinksUpdate\LinksUpdate;
use MediaWiki\Extension\NotifyMe\EventFactory;
use MediaWiki\Hook\LinksUpdateCompleteHook;
use MediaWiki\Hook\PageMoveCompleteHook;
use MediaWiki\Page\Hook\PageDeleteCompleteHook;
use MediaWiki\Page\ProperPageIdentity;
use MediaWiki\Permissions\Authority;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Storage\Hook\PageSaveCompleteHook;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\Hook\UserGroupsChangedHook;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\Events\BotAgent;
use MWStake\MediaWiki\Component\Events\Notifier;

class TriggerEvents implements
	PageSaveCompleteHook,
	PageMoveCompleteHook,
	PageDeleteCompleteHook,
	LinksUpdateCompleteHook,
	UserGroupsChangedHook

{

	/**
	 * @var Notifier
	 */
	private $notifier;

	/**
	 * @var EventFactory
	 */
	private $eventFactory;

	/**
	 * @var UserFactory
	 */
	private $userFactory;

	/**
	 * @var TitleFactory
	 */
	private $titleFactory;

	/**
	 * @var RevisionLookup
	 */
	private $revisionLookup;

	/**
	 * @param Notifier $notifier
	 * @param EventFactory $eventFactory
	 * @param UserFactory $userFactory
	 * @param TitleFactory $titleFactory
	 * @param RevisionLookup $revisionLookup
	 */
	public function __construct(
		Notifier $notifier, EventFactory $eventFactory, UserFactory $userFactory, TitleFactory $titleFactory,
		RevisionLookup $revisionLookup
	) {
		$this->notifier = $notifier;
		$this->eventFactory = $eventFactory;
		$this->userFactory = $userFactory;
		$this->titleFactory = $titleFactory;
		$this->revisionLookup = $revisionLookup;
	}

	/**
	 * @inheritDoc
	 */
	public function onPageSaveComplete( $wikiPage, $user, $summary, $flags, $revisionRecord, $editResult ) {
		$agent = $this->getAgent( $user );
		$title = $this->titleFactory->castFromPageIdentity( $revisionRecord->getPage() );
		if ( $title->isRedirect() ) {
			return;
		}
		if ( $editResult->isNullEdit() ) {
			return;
		}
		if ( $editResult->isRevert() ) {
			$firstRevert = $editResult->getOldestRevertedRevisionId();
			$revision = $this->revisionLookup->getRevisionById( $firstRevert );
			if ( !$revision ) {
				return;
			}

			$affectedUsers = [];
			while ( $revision ) {
				$revisionUser = $revision->getUser();
				if ( $revisionUser ) {
					$affectedUsers[$revisionUser->getId()] = $revisionUser;
				}
				$revision = $this->revisionLookup->getNextRevision( $revision );
			}
			if ( empty( $affectedUsers ) ) {
				return;
			}
			$affectedUsers = array_values( $affectedUsers );
			$affectedUsers = array_map( function ( UserIdentity $user ) {
				return $this->userFactory->newFromUserIdentity( $user );
			}, $affectedUsers );
			$this->emit( 'page-edit-revert', [
				$affectedUsers,
				$agent,
				$title,
				$revisionRecord->getId(),
				$firstRevert
			] );
			return;
		}

		if ( !$title->isTalkPage() ) {
			if ( $flags & EDIT_NEW ) {
				$this->emit( 'page-create', [ $agent, $title ] );
			} else {
				if ( $flags & EDIT_MINOR ) {
					// Do not notify for minor
					return;
				}
				$previousRevision = $this->revisionLookup->getPreviousRevision( $revisionRecord );
				$this->emit( 'page-edit', [
					$agent,
					$title,
					$revisionRecord->getId(),
					$previousRevision ? $previousRevision->getId() : null
				] );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onPageMoveComplete( $old, $new, $user, $pageid, $redirid, $reason, $revision ) {
		$old = $this->titleFactory->castFromLinkTarget( $old );
		$new = $this->titleFactory->castFromLinkTarget( $new );
		$agent = $this->getAgent( $user );

		$this->emit( 'page-move', [ $agent, $new, $old ] );
	}

	/**
	 * @inheritDoc
	 */
	public function onPageDeleteComplete(
		ProperPageIdentity $page, Authority $deleter, string $reason, int $pageID, RevisionRecord $deletedRev,
		ManualLogEntry $logEntry, int $archivedRevisionCount
	) {
		$title = $this->titleFactory->castFromPageIdentity( $page );
		$agent = $this->getAgent( $deleter->getUser() );
		$this->emit( 'page-delete', [ $agent, $title ] );
	}

	/**
	 * @param UserIdentity $user
	 *
	 * @return UserIdentity
	 */
	private function getAgent( UserIdentity $user ): UserIdentity {
		$agent = $this->userFactory->newFromUserIdentity( $user );
		if ( $agent->isSystemUser() ) {
			$agent = new BotAgent();
		}
		return $agent;
	}

	/**
	 * @param string $key
	 * @param array $args
	 * @return void
	 * @throws Exception
	 */
	private function emit( string $key, array $args ) {
		$event = $this->eventFactory->create( $key, $args );
		$this->notifier->emit( $event );
	}

	/**
	 * @param LinksUpdate $linksUpdate
	 * @param mixed $ticket
	 * @return void
	 * @throws Exception
	 */
	public function onLinksUpdateComplete( $linksUpdate, $ticket ) {
		$fromTitle = $linksUpdate->getTitle();
		$revRecord = $linksUpdate->getRevisionRecord();
		if ( !$revRecord || $fromTitle->isRedirect() || !$fromTitle->isContentPage() ) {
			return;
		}
		$agent = $this->getAgent( $revRecord->getUser() );
		$addedLinks = $linksUpdate->getPageReferenceArray( 'pagelinks', LinksTable::INSERTED );
		foreach ( $addedLinks as $addedLink ) {
			$title = Title::castFromPageReference( $addedLink );
			if ( !$title || !$title->isContentPage() || $title->isRedirect() ) {
				continue;
			}
			$firstRevision = $this->revisionLookup->getFirstRevision( $title );
			if ( !$firstRevision ) {
				continue;
			}
			$creatorIdentity = $firstRevision->getUser();
			if ( !$creatorIdentity ) {
				continue;
			}
			$creator = $this->userFactory->newFromUserIdentity( $creatorIdentity );
			$this->emit( 'page-linked', [ $agent, $title, $creator, $fromTitle ] );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function onUserGroupsChanged( $user, $added, $removed, $performer, $reason, $oldUGMs, $newUGMs ) {
		$targetUser = $this->userFactory->newFromName( $user );
		$agent = $this->getAgent( $performer );
		if ( !empty( $added ) ) {
			$this->emit( 'user-group-added', [ $agent, $targetUser, $added ] );
		}
		if ( !empty( $removed ) ) {
			$this->emit( 'user-group-removed', [ $agent, $targetUser, $removed ] );
		}
	}
}
