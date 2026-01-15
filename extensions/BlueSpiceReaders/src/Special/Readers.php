<?php

namespace BlueSpice\Readers\Special;

use MediaWiki\Html\Html;
use MediaWiki\Output\OutputPage;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Title\Title;
use MediaWiki\User\UserFactory;

class Readers extends SpecialPage {

	/** @var UserFactory */
	protected $userFactory;

	/**
	 * @param UserFactory $userFactory
	 */
	public function __construct( UserFactory $userFactory ) {
		parent::__construct( 'Readers', 'viewreaders', false );
		$this->userFactory = $userFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		$out = $this->getOutput();
		$requestParams = $this->getRequest()->getQueryValues();

		if ( isset( $requestParams['user'] ) ) {
			$this->readersByUser( $out, $requestParams['user'] );
		} elseif ( isset( $requestParams['page'] ) ) {
			$this->readersOfPage( $out, $requestParams['page'] );
		} else {
			$out->setPageTitle( $this->msg( 'bs-readers-emptyinput' ) );
			$out->addHTML( $this->msg( 'bs-readers-emptyinput' )->text() );
		}
	}

	/**
	 * @param OutputPage $out
	 * @param string $username
	 */
	private function readersByUser( OutputPage $out, string $username ) {
		$user = $this->userFactory->newFromName( $username );
		if ( !$user ) {
			$this->pageNotExistError( $out );
			return;
		}

		$out->addJsConfigVars( 'bsReadersUserID', $user->getId() );
		$out->setPageTitle(	$this->msg( 'readers-user', $username ) );
		$out->addModules( [ 'ext.bluespice.readers.specialReadersUser' ] );
		$out->addHTML( Html::element( 'div', [ 'id' => 'bs-readers-special-readers-user-container' ] ) );
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

		$out->addJsConfigVars( 'bsReadersTitle', $page );
		$out->setPageTitle(	$this->msg( 'readers', $page ) );
		$out->addModules( [ 'ext.bluespice.readers.specialReadersPage' ] );
		$out->addHTML( Html::element( 'div', [ 'id' => 'bs-readers-special-readers-page-container' ] ) );
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
