<?php

namespace MWStake\MediaWiki\Component\Wire;

use MediaWiki\Page\PageIdentity;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\User\UserIdentity;

class WireChannelFactory {

	/**
	 * @return WireChannel
	 */
	public function getGlobalChannel(): WireChannel {
		return $this->makeChannel( 'global' );
	}

	/**
	 * @param UserIdentity $user
	 * @return WireChannel
	 */
	public function getChannelForUser( UserIdentity $user ): WireChannel {
		return $this->makeChannel( 'user-' . $user->getName() );
	}

	/**
	 * @param SpecialPage $specialPage
	 * @return WireChannel
	 */
	public function getChannelForSpecialPage( SpecialPage $specialPage ): WireChannel {
		return $this->makeChannel( 'special-' . $specialPage->getName() );
	}

	/**
	 * @param PageIdentity $page
	 * @return WireChannel
	 */
	public function getChannelForPage( PageIdentity $page ) {
		return $this->makeChannel( 'page-' . $page->getNamespace() . '|' . $page->getDBkey() );
	}

	/**
	 * @param string $key
	 * @return WireChannel
	 */
	private function makeChannel( string $key ): WireChannel {
		return new WireChannel( $key );
	}
}
