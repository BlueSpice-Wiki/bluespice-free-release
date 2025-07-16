<?php

namespace MediaWiki\Extension\UserProfile;

use Exception;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Content\JsonContent;
use MediaWiki\Content\WikitextContent;
use MediaWiki\Extension\UserProfile\Content\Profile;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Message\Message;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Permissions\Authority;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Revision\RevisionAccessException;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserIdentity;
use PermissionsError;
use Psr\Log\LoggerInterface;
use Throwable;

class ProfileManager {

	/** @var HookContainer */
	protected $hookContainer;

	/** @var PermissionManager */
	protected $permissionManager;

	/** @var UserFactory */
	protected $userFactory;

	/** @var WikiPageFactory */
	protected $wikiPageFactory;

	/** @var ProfileFieldRegistry */
	protected $profileFieldRegistry;

	/** @var LoggerInterface */
	protected $logger;

	/**
	 * @param HookContainer $hookContainer
	 * @param PermissionManager $permissionManager
	 * @param UserFactory $userFactory
	 * @param WikiPageFactory $wikiPageFactory
	 * @param ProfileFieldRegistry $profileFieldRegistry
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		HookContainer $hookContainer, PermissionManager $permissionManager, UserFactory $userFactory,
		WikiPageFactory $wikiPageFactory, ProfileFieldRegistry $profileFieldRegistry, LoggerInterface $logger
	) {
		$this->hookContainer = $hookContainer;
		$this->permissionManager = $permissionManager;
		$this->userFactory = $userFactory;
		$this->wikiPageFactory = $wikiPageFactory;
		$this->profileFieldRegistry = $profileFieldRegistry;
		$this->logger = $logger;
	}

	/**
	 * @param UserIdentity $user
	 * @param Authority $requester
	 * @param bool $forEditing
	 * @return array
	 */
	public function getProfileData( UserIdentity $user, Authority $requester, bool $forEditing = false ): array {
		$data = $this->getRawProfileData( $user );

		$this->hookContainer->run( 'UserProfileGetProfileData', [ &$data, $user, $requester ] );
		return $this->censorData( $data, $user, $requester, $forEditing );
	}

	/**
	 * @param UserIdentity $user
	 * @return array
	 */
	public function getRawProfileData( UserIdentity $user ): array {
		$user = $this->userFactory->newFromUserIdentity( $user );
		$wp = $this->wikiPageFactory->newFromTitle( $user->getUserPage() );
		$rev = $wp->getRevisionRecord();
		if ( $rev ) {
			try {
				$content = $rev->getContent( SLOT_ROLE_USER_PROFILE );
				if ( $content instanceof JsonContent && $content->isValid() ) {
					return json_decode( $content->getText(), true );
				}
			} catch ( RevisionAccessException $ex ) {
				return [];
			}
		}

		return [];
	}

	/**
	 * @param array $data
	 * @param UserIdentity $forUser
	 * @param Authority $actor
	 * @return void
	 * @throws PermissionsError
	 */
	public function setProfileData( array $data, UserIdentity $forUser, Authority $actor ) {
		$this->assertActorCan( 'edit', $forUser, $actor );
		$data = $this->filterData( $data );
		$this->hookContainer->run( 'UserProfileBeforeSetProfileData', [ &$data, $forUser, $actor ] );
		$user = $this->userFactory->newFromUserIdentity( $forUser );
		$userPage = $user->getUserPage();
		$content = new Profile( json_encode( $data ) );
		$status = $this->doEdit( $userPage, $content, $actor );
		if ( !$status->isOK() ) {
			$errors = $status->getErrors();
			$messages = [];
			foreach ( $errors as $error ) {
				$messages[] = Message::newFromKey( $error['message'] )->params( ...$error['params'] )->parse();
			}
			$this->logger->error( 'Failed to store profile data: {messages}', [ 'messages' => $messages ] );
			throw new Exception(
				Message::newFromKey( 'userprofile-save-error' )
					->params( implode( "<br>", $messages ) )
					->parse()
			);
		}
		$this->hookContainer->run( 'UserProfileAfterSetProfileData', [ $data, $user ] );
		$this->logger->info( 'Profile data for {user} updated by {actor}', [
			'user' => $user->getName(),
			'actor' => $actor->getUser()->getName()
		] );
	}

	/**
	 * @return ProfileFieldRegistry
	 */
	public function getFieldRegistry(): ProfileFieldRegistry {
		return $this->profileFieldRegistry;
	}

	/**
	 * @param Title $userPage
	 * @param Profile $content
	 * @param Authority $actor
	 * @return Status
	 */
	private function doEdit( Title $userPage, Profile $content, Authority $actor ): Status {
		$wp = $this->wikiPageFactory->newFromTitle( $userPage );
		$updater = $wp->newPageUpdater( $actor );
		if ( !$userPage->exists() ) {
			$updater->setContent( SlotRecord::MAIN, new WikitextContent( '' ) );
		}
		$updater->setContent( CONTENT_MODEL_USER_PROFILE, $content );
		try {
			$updater->saveRevision( CommentStoreComment::newUnsavedComment(
				Message::newFromKey( 'userprofile-edit-summary' )
			) );
		} catch ( Throwable $ex ) {
			return Status::newFatal( $ex->getMessage() );
		}

		return $updater->getStatus();
	}

	/**
	 * @param string $action
	 * @param UserIdentity $user
	 * @param Authority $actor
	 * @return void
	 * @throws PermissionsError
	 */
	public function assertActorCan( string $action, UserIdentity $user, Authority $actor ) {
		$user = $this->userFactory->newFromUserIdentity( $user );
		if ( $action === 'edit' && !$this->canEdit( $user, $actor ) ) {
			$this->permissionManager->throwPermissionErrors( 'wikiadmin', $actor, $user->getUserPage() );
		}
	}

	/**
	 * @param User $owner
	 * @param Authority $actor
	 * @return bool
	 */
	public function canEdit( User $owner, Authority $actor ): bool {
		$actor = $this->userFactory->newFromUserIdentity( $actor->getUser() );
		if ( $actor->isSystemUser() ) {
			return true;
		}
		return $this->permissionManager->userCan( 'edit', $actor, $owner->getUserPage() ) &&
			(
				$owner->getName() === $actor->getName() ||
				$this->permissionManager->userHasRight( $actor, 'userprofile-edit-other' )
			);
	}

	/**
	 * @param Title $page
	 * @return User|null
	 */
	public function getUserFromTitle( Title $page ): ?User {
		if ( $page->getNamespace() !== NS_USER || $page->isSubpage() ) {
			return null;
		}
		$user = $this->userFactory->newFromName( $page->getText() );
		if ( !$user || !$user->isRegistered() ) {
			return null;
		}
		return $user;
	}

	/**
	 * @return LoggerInterface
	 */
	public function getLogger(): LoggerInterface {
		return $this->logger;
	}

	/**
	 * @param array $data
	 * @param User $user
	 * @param Authority $requester
	 * @param bool $forEditing
	 * @return array
	 */
	private function censorData( array $data, User $user, Authority $requester, bool $forEditing ): array {
		$isOwn = $user->getName() === $requester->getUser()->getName();
		if ( $isOwn ) {
			return $data;
		}

		if ( $forEditing ) {
			$fields = $this->profileFieldRegistry->getEditableFields();
		} else {
			$fields = $this->profileFieldRegistry->getPublicFields( $user );
		}
		foreach ( $data as $key => $value ) {
			if ( !in_array( $key, $fields ) ) {
				unset( $data[$key] );
			}
		}
		return $data;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	private function filterData( array $data ): array {
		$fields = $this->profileFieldRegistry->getAllowedFields();
		$filtered = [];
		foreach ( $data as $key => $value ) {
			if ( $value && in_array( $key, $fields ) ) {
				$filtered[$key] = $value;
			}
		}
		return $filtered;
	}
}
