<?php

namespace BlueSpice\Discovery\Component;

use BlueSpice\Discovery\ILastEditInfoModifier;
use BlueSpice\Timestamp;
use DateTime;
use MediaWiki\Context\RequestContext;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\RevisionStore;
use MediaWiki\Title\Title;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;
use Wikimedia\ObjectFactory\ObjectFactory;

class LastEditInfo extends Literal {

	/**
	 *
	 * @var RequestContext
	 */
	private $requestContext = null;

	/**
	 *
	 * @var LinkRenderer
	 */
	private $linkRenderer = null;

	/**
	 *
	 * @var RevisionStore
	 */
	private $revisionStore = null;

	/**
	 * @var ObjectFactory
	 */
	private $objectFactory = null;

	/** @var MediaWikiServices */
	private $services = null;

	/**
	 *
	 * @param RequestContext $requestContext
	 * @param LinkRenderer $linkRenderer
	 * @param RevisionStore $revisionStore
	 */
	public function __construct( $requestContext, $linkRenderer, $revisionStore, $objectFactory ) {
		parent::__construct(
			'last-edit-info',
			''
		);

		$this->requestContext = $requestContext;
		$this->linkRenderer = $linkRenderer;
		$this->revisionStore = $revisionStore;
		$this->objectFactory = $objectFactory;

		$this->services = MediaWikiServices::getInstance();
	}

	/**
	 * Raw HTML string
	 *
	 * @return string
	 */
	public function getHtml(): string {
		return $this->buildHtml();
	}

	/**
	 *
	 * @return string
	 */
	private function buildHtml(): string {
		$html = '';
		$this->requestContext = RequestContext::getMain();
		$title = $this->requestContext->getTitle();
		if ( !$title || $title->isSpecialPage() ) {
			return $html;
		}

		$revision = $this->revisionStore->getRevisionByTitle( $title );
		if ( !$revision ) {
			return $html;
		}

		$revisionDiffLink = $this->buildRevisionDiffLink( $title, $revision );
		$lastEditorLink = $this->buildLastEditorLink( $revision );

		if ( $lastEditorLink ) {
			$lastEditInfo = Message::newFromKey(
				'bs-discovery-title-last-edit-info',
				$revisionDiffLink,
				$lastEditorLink
			);
		} else {
			// RevisionDelete user
			$lastEditInfo = Message::newFromKey(
				'bs-discovery-title-last-edit-info-date-only',
				$revisionDiffLink
			);
		}

		$this->services->getHookContainer()->run(
			'LastEditInfo',
			[ $revision, $revisionDiffLink, $lastEditorLink, &$lastEditInfo ]
		);

		if ( $lastEditInfo ) {
			$html .= $lastEditInfo->text();
		}

		$registry = ExtensionRegistry::getInstance()->getAttribute(
			'BlueSpiceDiscoveryLastEditInfoModifier'
		);

		if ( !empty( $registry ) ) {
			ksort( $registry );
			$spec = array_pop( $registry );

			$modifier = $this->objectFactory->createObject( $spec, [ $html ] );
			if ( $modifier instanceof ILastEditInfoModifier ) {
				$html = $modifier->getHtml( $html );
			}
		}

		return $html;
	}

	/**
	 *
	 * @param Title $title
	 * @param RevisionRecord|null $revision
	 * @return string
	 */
	private function buildRevisionDiffLink( $title, $revision ): string {
		$html = '';
		$rawTimestamp = $revision->getTimestamp();
		/** @var Timestamp */
		$revisionTimestamp = Timestamp::getInstance( $rawTimestamp );
		$timestamp = $revisionTimestamp->getAgeString( null, null, 1 );
		$ariaLabel = Message::newFromKey(
			'bs-discovery-title-last-edit-info-timestamp-aria-label',
			$timestamp
		);

		$date = new DateTime();
		$date->setTimestamp( strtotime( $rawTimestamp ) );
		$language = $this->services->getContentLanguage();
		$exactTime = $language->userTimeAndDate( $date, $this->requestContext->getUser() );

		$html = $this->linkRenderer->makeLink(
			$title,
			$timestamp,
			[
				'title' => $exactTime,
				'aria-label' => $ariaLabel->plain(),
				'role' => 'link',
				'rel' => 'nofollow',
			],
			[
				'oldid' => $revision->getId(),
				'diff' => 'prev'
			]
		);
		return $html;
	}

	/**
	 *
	 * @param RevisionRecord|null $revision
	 * @return string
	 */
	private function buildLastEditorLink( $revision ): string {
		$html = '';
		$userPerformer = $this->requestContext->getUser();
		$userIdentity = $revision->getUser( RevisionRecord::FOR_THIS_USER, $userPerformer );

		// RevisionDelete user
		if ( !$userIdentity ) {
			return '';
		}

		$user = $this->services->getUserFactory()->newFromId( $userIdentity->getId() );
		$username = $user->getName();

		/* Main_page is created with user id 0 */
		if ( $userIdentity->getId() === 0 ) {

			if ( $user->isSystemUser() ) {
				$html = Message::newFromKey( 'bs-discovery-last-edit-by-system-user' );
				return $html->text();
			}

			if ( $user->isAnon() ) {
				$html = Message::newFromKey( 'bs-discovery-last-edit-by-anon-user' );
				return $html->text();
			}

		}

		$ariaLabelMsg = Message::newFromKey(
			'bs-discovery-title-last-edit-info-user-aria-label',
			$username
		);
		$makeLinkMethod = in_array(
			$username,
			$GLOBALS['wgReservedUsernames'],
			true
		) ? 'makeKnownLink' : 'makeLink';
		$html = $this->linkRenderer->$makeLinkMethod(
			$user->getUserPage(),
			$username,
			[
				'aria-label' => $ariaLabelMsg->plain(),
				'role' => 'link'
			],
			[]
		);
		return $html;
	}
}
