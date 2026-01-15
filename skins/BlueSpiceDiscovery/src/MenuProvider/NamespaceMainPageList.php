<?php

namespace BlueSpice\Discovery\MenuProvider;

use BlueSpice\Discovery\Component\NamespaceMainPages;
use BlueSpice\Discovery\IMenuProvider;
use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;
use MediaWiki\Page\PageProps;
use MWStake\MediaWiki\Component\CommonUserInterface\IComponent;

class NamespaceMainPageList implements IMenuProvider {

	/** @var PageProps */
	private $pageProps = null;

	/**
	 * @param PageProps $pageProps
	 */
	public function __construct( PageProps $pageProps ) {
		$this->pageProps = $pageProps;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return 'namespace-mainpage-list';
	}

	/**
	 * @return Message
	 */
	public function getLabelMsg(): Message {
		return new Message( 'bs-discovery-menu-provider-namespace-mainpage-list-label' );
	}

	/**
	 * @return Message
	 */
	public function getDescriptionMsg(): Message {
		return new RawMessage( 'bs-discovery-menu-provider-namespace-mainpage-list-desc' );
	}

	/**
	 * @return IComponent
	 */
	public function getComponent(): IComponent {
		return new NamespaceMainPages( $this->pageProps );
	}
}
