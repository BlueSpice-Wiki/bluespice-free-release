<?php

namespace BlueSpice\RSSFeeder\Special;

use BlueSpice\RSSFeeder\RSSFeedManager;
use BlueSpice\RSSFeeder\RSSTokenProvider;
use MediaWiki\Context\DerivativeContext;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\SpecialPage\SpecialPage;
use RSSAuthenticator;
use Wikimedia\AtEase\AtEase;

class RSSFeeder extends SpecialPage {

	/**
	 * @param RSSTokenProvider $tokenProvider
	 */
	public function __construct(
		private readonly RSSTokenProvider $tokenProvider
	) {
		parent::__construct( 'RSSFeeder' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute( $subpage ) {
		$isRegistered = $this->getUser()->isRegistered();
		$feedContext = $this->getContext();
		if ( !$isRegistered ) {
			// Feed readers identify the user by name and token. This only gives
			// access to the requested feed, for this request. It does not log in
			// the user.
			$authenticator = new RSSAuthenticator(
				$this->getRequest(), $this->getContext(), $this->tokenProvider
			);
			$tokenUser = $authenticator->getUserFromRequest();
			if ( $tokenUser ) {
				$feedContext = new DerivativeContext( $this->getContext() );
				$feedContext->setUser( $tokenUser );
			}
		}

		parent::execute( $subpage );

		$feedsManager = MediaWikiServices::getInstance()->getService( 'BSRSSFeederFeedManagerFactory' )
			->makeManager( $feedContext, $feedContext->getUser() );

		$this->showFeed( $subpage, $feedsManager );

		if ( $isRegistered ) {
			$this->showConfig();
		}
	}

	/**
	 * @param string $params
	 * @param RSSFeedManager $feedsManager
	 */
	private function showFeed( $params, RSSFeedManager $feedsManager ) {
		$extension = false;

		if ( $params ) {
			$params = $this->parseParams( $params );
		} else {
			$params = [
				'Page' => $this->getRequest()->getVal( 'Page', '' )
			];
		}
		if ( isset( $params['Page'] ) ) {
			$extension = $params['Page'];
		}

		$requestedFeed = $feedsManager->getFeed( $extension );
		if ( $requestedFeed ) {
			$this->getOutput()->disable();
			AtEase::suppressWarnings();
			header( 'Content-Type: application/xml; charset=UTF-8' );
			echo $requestedFeed->getRss();
			AtEase::restoreWarnings();
			return;
		}
	}

	private function showConfig() {
		$out = $this->getOutput();

		$out->addModuleStyles( 'ext.bluespice.rssfeeder.styles' );
		$out->addModules( [ 'ext.bluespice.rssfeeder.specialRSSFeeder' ] );
		$out->addJsConfigVars( 'bsRSSFeederUserAuth', $this->getUserAuth() );
		$out->addHTML( Html::element( 'div', [ 'id' => 'bs-rssfeeder-special-rssfeeder-container' ] ) );
	}

	/**
	 * @param string $params
	 * @return array
	 */
	protected function parseParams( $params ) {
		$parsedParams = [];
		$aTokens = explode( '/', $params );
		foreach ( $aTokens as $vKeyValuePairs ) {
			$vKeyValuePairs = explode( ':', $vKeyValuePairs );
			$parsedParams[$vKeyValuePairs[0]] = $vKeyValuePairs[1];
		}
		return $parsedParams;
	}

	/**
	 * @return array
	 */
	private function getUserAuth(): array {
		$token = $this->tokenProvider->getRSSToken( $this->getUser() );

		return [
			'h' => $token,
			'u' => $this->getUser()->getName()
		];
	}
}
