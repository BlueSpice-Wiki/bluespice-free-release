<?php

namespace BlueSpice\Discovery\Component;

use BlueSpice\Discovery\ILangLinksProvider;
use BlueSpice\Discovery\LangLinksProviderFactory;
use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleDropdownIcon;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleDropdownItemlistFromArray;
use MWStake\MediaWiki\Component\CommonUserInterface\LinkFormatter;

class TitleActionLanguage extends SimpleDropdownIcon {

	/** @var array */
	private $wikitextLangLinks = [];

	/** @var array */
	private $languageLinks = [];

	/**
	 * @param array $componentProcessData
	 */
	public function __construct( $componentProcessData ) {
		parent::__construct( [] );

		if ( isset( $componentProcessData['panel'] )
			&& isset( $componentProcessData['panel']['languages'] ) ) {
				$this->wikitextLangLinks = $componentProcessData['panel']['languages'];
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return 'ta-language-btn';
	}

	/**
	 * @return array
	 */
	public function getContainerClasses(): array {
		return $this->options['container-classes'];
	}

	/**
	 * @return array
	 */
	public function getButtonClasses(): array {
		$classes = [ 'ico-btn' ];
		$links = $this->languageLinks;
		if ( empty( $links ) ) {
			array_push( $classes, 'disabled' );
		}
		return $classes;
	}

	/**
	 * @return array
	 */
	public function getMenuClasses(): array {
		return [ 'mws-dropdown-secondary', 'dropdown-menu-end' ];
	}

	/**
	 * @return array
	 */
	public function getIconClasses(): array {
		return [ 'bi-globe2' ];
	}

	/**
	 * @return Message
	 */
	public function getTitle(): Message {
		return Message::newFromKey( 'bs-discovery-title-action-language-button-title' );
	}

	/**
	 * @return Message
	 */
	public function getAriaLabel(): Message {
		return Message::newFromKey( 'bs-discovery-title-action-language-button-aria-label' );
	}

	/**
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		$this->init();
		return [
			new SimpleDropdownItemlistFromArray( [
				'id' => 'new-content-itms',
				'classes' => [],
				'links' => $this->languageLinks
			] )
		];
	}

	/**
	 * @return ILangLinksProvider
	 */
	private function getLangLinksProvider(): ILangLinksProvider {
		$services = MediaWikiServices::getInstance();
		/** @var LangLinksProviderFactory */
		$langLinksProviderFactory = $services->get( 'BlueSpiceDiscoveryLangLinksProviderFactory' );

		return $langLinksProviderFactory->create( $this->wikitextLangLinks );
	}

	/**
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool {
		$this->init();
		if ( !empty( $this->languageLinks ) ) {
			return true;
		}
		return false;
	}

	/** @var bool */
	private $alreadyInited = false;

	/**
	 * @return void
	 */
	private function init(): void {
		if ( $this->alreadyInited ) {
			return;
		}

		$links = $this->getLangLinksProvider()->getLangLinks(
			$this->wikitextLangLinks,
			RequestContext::getMain()->getTitle()
		);
		if ( empty( $links ) ) {
			return;
		}

		$services = MediaWikiServices::getInstance();
		/** @var LinkFormatter */
		$linkFormatter = $services->getService( 'MWStakeLinkFormatter' );
		$this->languageLinks = $linkFormatter->formatLinks( $links );

		$this->alreadyInited = true;
	}
}
