<?php

namespace BlueSpice\Discovery\Component;

use BlueSpice\Discovery\BackLinkProviderFactory;
use BlueSpice\Discovery\IBackLinkProvider;
use MediaWiki\Context\IContextSource;
use MediaWiki\Message\Message;
use MediaWiki\SpecialPage\SpecialPage;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleLink;

class BackTo extends SimpleLink {

	/** @var IBackLinkProvider */
	private $provider = null;

	/** @var BackLinkProviderFactory */
	private $backLinkProviderFactory;

	/**
	 *
	 */
	public function __construct( BackLinkProviderFactory $backLinkProviderFactory ) {
		parent::__construct( [] );

		$this->provider = null;
		$this->backLinkProviderFactory = $backLinkProviderFactory;
	}

	public function getClasses(): array {
		return [ 'backto-link', 'bi-arrow-left' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return 'backTo';
	}

	/**
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		return [
			new Literal(
				'backTo-label',
				$this->provider->getLabel()
			)
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getRole(): string {
		return 'link';
	}

	/**
	 * @inheritDoc
	 */
	public function getTitle(): Message {
		return $this->provider->getTitle();
	}

	/**
	 * @inheritDoc
	 */
	public function getAriaLabel(): Message {
		return $this->provider->getAriaLabel();
	}

	/**
	 * @inheritDoc
	 */
	public function getHref(): string {
		return $this->provider->getHref();
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool {
		$provider = $this->backLinkProviderFactory->getProvider( $context );
		if ( !$provider ) {
			return false;
		}
		$specialUserLogin = SpecialPage::getSafeTitleFor( 'Userlogin' );
		$title = $context->getTitle();
		if ( $specialUserLogin->equals( $title ) ) {
			return false;
		}
		$this->provider = $provider;
		return true;
	}

}
