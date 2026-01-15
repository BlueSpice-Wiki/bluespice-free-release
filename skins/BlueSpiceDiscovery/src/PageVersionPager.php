<?php

namespace BlueSpice\Discovery;

use MediaWiki\Html\Html;
use MediaWiki\Language\Language;
use MediaWiki\Message\Message;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

class PageVersionPager {

	/** @var Title */
	private $title;

	/** @var RevisionLookup */
	protected $revisionLookup;

	/** @var Language */
	protected $userLang = null;

	/**
	 * @param Title $title
	 * @param RevisionLookup $revisionLookup
	 * @param Language $userLang
	 */
	public function __construct( Title $title, RevisionLookup $revisionLookup, Language $userLang ) {
		$this->title = $title;
		$this->revisionLookup = $revisionLookup;
		$this->userLang = $userLang;
	}

	/**
	 * @param RevisionRecord|null $currentRevision
	 * @return RevisionRecord|null
	 */
	private function getNextRevisionRecord( ?RevisionRecord $currentRevision ): ?RevisionRecord {
		return $this->revisionLookup->getNextRevision( $currentRevision );
	}

	 /**
	  * @param RevisionRecord|null $currentRevision
	  * @return RevisionRecord|null
	  */
	private function getPreviousRevisionRecord( ?RevisionRecord $currentRevision ): ?RevisionRecord {
		return $this->revisionLookup->getPreviousRevision( $currentRevision );
	}

	/**
	 * @param RevisionRecord|null $currentRevision
	 * @return array
	 */
	private function getNextRevisionButtonParams( ?RevisionRecord $currentRevision ): array {
		$params = [
			'id' => 'bs-page-version-pager-next',
		];

		$nextRevision = $this->getNextRevisionRecord( $currentRevision );
		if ( !$nextRevision ) {
			$params['disabled'] = 'true';
		} else {
			$nextRevisionId = $nextRevision->getId();
			$params['href'] = $this->title->getLinkURL( [ 'oldid' => $nextRevisionId ] );
		}

		return $params;
	}

	 /**
	  * @param RevisionRecord|null $currentRevision
	  * @return array
	  */
	private function getPreviousRevisionButtonParams( ?RevisionRecord $currentRevision ): array {
		$params = [
			'id' => 'bs-page-version-pager-previous',
		];

		$previousRevision = $this->getPreviousRevisionRecord( $currentRevision );
		if ( !$previousRevision ) {
			$params['disabled'] = 'true';
		} else {
			$previousRevisionId = $previousRevision->getId();
			$params['href'] = $this->title->getLinkURL( [ 'oldid' => $previousRevisionId ] );
		}

		return $params;
	}

	/**
	 * @param RevisionRecord|null $currentRevision
	 * @return string
	 */
	private function getRevisionInfo( ?RevisionRecord $currentRevision ): string {
		if ( !$currentRevision ) {
			return '';
		}

		/** @var User */
		$user = $currentRevision->getUser();
		$userName = $user
			? $user->getName()
			: Message::newFromKey( 'bs-discovery-page-version-pager-deleted-user' )->plain();

		$timestamp = $currentRevision->getTimestamp();
		$date = $this->userLang->date( $timestamp, true );
		$time = $this->userLang->time( $timestamp, true );

		$message = Message::newFromKey( 'bs-discovery-page-version-pager-info-text' );
		$message->params( $date, $time, $userName );
		return $message->parse();
	}

	/**
	 * @param RevisionRecord|null $currentRevision
	 * @return string
	 */
	public function getHtml( $currentRevision ): string {
		$html = Html::openElement( 'div', [ 'class' => 'bs-revision-pager' ] );

		/** info */
		$html .= Html::openElement( 'div', [ 'class' => 'bs-page-version-pager-info' ] );
		$html .= $this->getRevisionInfo( $currentRevision );
		$html .= Html::closeElement( 'div' );

		/** toolbar */
		$html .= Html::openElement( 'div', [ 'class' => 'bs-page-version-pager-toolbar' ] );
		$html .= Html::element(
			'a',
			$this->getPreviousRevisionButtonParams( $currentRevision ),
			Message::newFromKey( 'bs-discovery-page-version-pager-previous' )->plain()
		);
		$html .= Html::element(
			'a',
			$this->getNextRevisionButtonParams( $currentRevision ),
			Message::newFromKey( 'bs-discovery-page-version-pager-next' )->plain()
		);
		$html .= Html::closeElement( 'div' );

		$html .= Html::closeElement( 'div' );

		return $html;
	}
}
