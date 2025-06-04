<?php

namespace MediaWiki\Extension\EnhancedUpload\Rest;

use Exception;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Content\WikitextContent;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\EnhancedUpload\AttachmentTagModifier;
use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Rest\HttpException;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\User;
use Wikimedia\ParamValidator\ParamValidator;

class SetAttachments extends SimpleHandler {
	protected const INPUT_PAGE_ID = 'pageId';
	protected const INPUT_COUNTER = 'counter';
	protected const INPUT_FILES = 'files';

	/**
	 * @var AttachmentTagModifier
	 */
	private $attachmentModifier;

	/**
	 * @var TitleFactory
	 */
	private $titleFactory;

	/**
	 * @var PermissionManager
	 */
	private $permissionManager;

	/**
	 * @param AttachmentTagModifier $attachmentModifier
	 * @param TitleFactory $titleFactory
	 * @param PermissionManager $permissionManager
	 */
	public function __construct( AttachmentTagModifier $attachmentModifier,
		TitleFactory $titleFactory, PermissionManager $permissionManager ) {
		$this->attachmentModifier = $attachmentModifier;
		$this->titleFactory = $titleFactory;
		$this->permissionManager = $permissionManager;
	}

	/**
	 * @param int $pageId
	 * @param int $counter
	 *
	 * @return true
	 * @throws HttpException
	 */
	public function run( int $pageId, int $counter ) {
		try {
			$title = $this->titleFactory->newFromID( $pageId );
			if ( !$title || !$title->exists() ) {
				throw new HttpException( 'Not Found', 404 );
			}

			$user = RequestContext::getMain()->getUser();
			if ( !$this->permissionManager->userCan( 'edit', $user, $title ) ) {
				throw new HttpException( 'Permission denied', 401 );
			}
			$request = $this->getRequest();
			$bodyContent = $request->getBody()->getContents();
			$files = json_decode( $bodyContent, true );

			$text = $this->getWikiText( $title );
			$wikitext = $this->attachmentModifier->add( $text, $counter, $files['files'] );
			$status = $this->savePage( $title, new WikitextContent( $wikitext ), $user );
			return $this->getResponseFactory()->createJson( [
				'success' => $status->isOK()
			] );
		} catch ( Exception $ex ) {
			throw new HttpException( $ex->getMessage(), $ex->getCode() );
		}
	}

	/** @inheritDoc */
	public function needsWriteAccess() {
		return true;
	}

	/** @inheritDoc */
	public function needsReadAccess() {
		return true;
	}

	/**
	 * @param string $name
	 * @param string|null $default
	 * @return string
	 */
	public function getParameter( $name, $default = null ) {
		$validated = $this->getValidatedParams();
		if ( isset( $validated[$name] ) ) {
			return $validated[$name];
		}

		return $default;
	}

	/**
	 * @return array[]
	 */
	public function getParamSettings() {
		return [
			self::INPUT_PAGE_ID => [
				static::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_REQUIRED => true,
			],
			self::INPUT_COUNTER => [
				static::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_REQUIRED => true,
			]
		];
	}

	/**
	 * @param Title $title
	 * @return string
	 */
	private function getWikiText( $title ) {
		$wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()
			->newFromTitle( $title );
		if ( !$wikiPage ) {
			return Status::newFatal( 'Invalid WikiPage' );
		}
		$content = $wikiPage->getContent();

		if ( !$content instanceof WikitextContent ) {
			return Status::newFatal( 'Not WikitextContent' );
		}
		$text = $content->getText();
		return $text;
	}

	/**
	 * @param Title $title
	 * @param WikitextContent $content
	 * @param User $user
	 * @return Status
	 */
	private function savePage( $title, WikitextContent $content, $user ): Status {
		$wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()
			->newFromTitle( $title );
		$revision = null;
		try {
			$updater = $wikiPage->newPageUpdater( $user );
			$updater->setContent( SlotRecord::MAIN, $content );
			$revision = $updater->saveRevision(
				// TODO: i18n
				CommentStoreComment::newUnsavedComment( 'Update Attachments' )
			);
		} catch ( Exception $e ) {
			return Status::newFatal( $e->getMessage() );
		}
		if ( !$revision ) {
			return $updater->getStatus();
		}
		return Status::newGood();
	}

}
