<?php

namespace BlueSpice\Readers\Special;

use MediaWiki\Html\Html;
use MediaWiki\Output\OutputPage;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;

class RevisionReaders extends SpecialPage {

	public function __construct() {
		parent::__construct( 'RevisionReaders', 'viewrevisionreaders', false );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		$out = $this->getOutput();
		$requestParams = $this->getRequest()->getQueryValues();

		if ( isset( $requestParams['page'] ) ) {
			$this->readersOfPage( $out, $requestParams['page'] );
		} else {
			$out->setPageTitle( $this->msg( 'bs-readers-emptyinput' ) );
			$out->addHTML( $this->msg( 'bs-readers-emptyinput' )->text() );
		}
	}

	/**
	 * @param OutputPage $out
	 * @param string $page
	 */
	private function readersOfPage( OutputPage $out, string $page ) {
		$title = Title::newFromText( $page );
		if ( !$title->exists() ) {
			$this->pageNotExistError( $out );
			return;
		}

		$out->addJsConfigVars( 'bsRevisionReadersPageId', $title->getArticleID() );
		$out->setPageTitle(	$this->msg( 'revisionreaders', $page ) );
		$out->addModules( [ 'ext.bluespice.readers.specialRevisionReaders' ] );
		$out->addHTML( Html::element( 'div', [ 'id' => 'bs-readers-special-revisionreaders-container' ] ) );
	}

	/**
	 * @param OutputPage $out
	 * @return string
	 */
	private function pageNotExistError( OutputPage $out ) {
		$out->setPageTitle( $this->msg( 'bs-readers-pagenotexists' ) );
		$out->addHTML( $this->msg( 'bs-readers-pagenotexists' )->text() );
	}
}
