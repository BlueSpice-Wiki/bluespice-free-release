<?php

namespace BlueSpice\Discovery\AlertProvider;

use BlueSpice\AlertProviderBase;
use BlueSpice\Discovery\PageVersionPager;
use BlueSpice\IAlertProvider;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Revision\RevisionRecord;

class PageVersionPagerAlert extends AlertProviderBase {

	/**
	 *
	 * @var RevisionRecord|null
	 */
	protected $oldRevisionRecord = null;

	/**
	 *
	 * @return string
	 */
	public function getHTML() {
		$title = $this->skin->getTitle();
		if ( !$title ) {
			return '';
		}
		$this->initOldId();

		$articleId = $title->getArticleID();
		$isBadTitle = $articleId === 0;
		if ( $this->isOldVersion() && !$isBadTitle ) {
			$message = Message::newFromKey( 'bs-discovery-alert-old-page-version' );
			$message->params( $articleId );

			$versionPagerHTML = $this->buildPageVersionPager();

			return $message->parse() . $versionPagerHTML;
		}

		return '';
	}

	/**
	 *
	 * @return string
	 */
	public function getType() {
		return IAlertProvider::TYPE_INFO;
	}

	/**
	 *
	 * @return bool
	 */
	private function isOldVersion() {
		if ( $this->oldRevisionRecord === null ) {
			return false;
		}
		$currentRevId = $this->skin->getTitle()->getLatestRevID();
		if ( $this->oldRevisionRecord->getId() === $currentRevId ) {
			return false;
		}

		return true;
	}

	/**
	 * @return string
	 */
	protected function buildPageVersionPager() {
		$services = MediaWikiServices::getInstance();
		$revisionLookup = $services->getRevisionLookup();
		$userLang = $this->skin->getLanguage();
		$title = $this->skin->getTitle();

		$pageVersionPager = new PageVersionPager( $title, $revisionLookup, $userLang );

		return $pageVersionPager->getHtml( $this->oldRevisionRecord );
	}

	/**
	 * Resolves the currently displayed "oldRevisionRecord" from the "old revision view" URL parameters
	 */
	private function initOldId() {
		$oldId = $this->skin->getRequest()->getInt( 'oldid', -1 );
		$revisionLookup = MediaWikiServices::getInstance()->getRevisionLookup();
		$this->oldRevisionRecord = $revisionLookup->getRevisionById( $oldId );
		if ( $this->oldRevisionRecord !== null ) {
			$direction = $this->skin->getRequest()->getVal( 'direction', '' );
			if ( $direction === 'next' ) {
				$this->oldRevisionRecord = $revisionLookup->getNextRevision( $this->oldRevisionRecord );
			}
			if ( $direction === 'prev' ) {
				$this->oldRevisionRecord = $revisionLookup->getPreviousRevision( $this->oldRevisionRecord );
			}
		}
	}
}
