<?php

namespace BlueSpice\Discovery\Structure;

use BaseTemplate;
use BlueSpice\Discovery\Component\FooterLinksListItems;
use BlueSpice\Discovery\IBaseTemplateAware;
use BlueSpice\Discovery\ITemplateDataProvider;
use BlueSpice\Discovery\Renderer\ComponentRenderer;
use BlueSpice\Discovery\Renderer\SkinSlotRenderer;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Revision\RevisionStore;
use MediaWiki\Title\TitleFactory;
use MediaWiki\Utils\UrlUtils;
use MWStake\MediaWiki\Component\CommonUserInterface\LinkFormatter;
use MWStake\MediaWiki\Component\Wikitext\ParserFactory;

class Footer extends SkinStructureBase implements IBaseTemplateAware {

	/** @var BaseTemplate */
	private $template;

	/** @var HookContainer */
	private $hookContainer;

	/** @var MediaWikiServices */
	private $services;

	/**
	 *
	 * @param ITemplateDataProvider $templateDataProvider
	 * @param ComponentRenderer $componentRenderer
	 * @param SkinSlotRenderer $skinSlotRenderer
	 * @param PermissionManager $permissionManager
	 * @param HookContainer $hookContainer
	 */
	public function __construct(
		ITemplateDataProvider $templateDataProvider,
		ComponentRenderer $componentRenderer,
		SkinSlotRenderer $skinSlotRenderer,
		PermissionManager $permissionManager,
		HookContainer $hookContainer ) {
		$this->hookContainer = $hookContainer;
		parent::__construct( $templateDataProvider, $componentRenderer, $skinSlotRenderer, $permissionManager );

		$this->services = MediaWikiServices::getInstance();
	}

	/**
	 *
	 * @param ITemplateDataProvider $templateDataProvider
	 * @param ComponentRenderer $componentRenderer
	 * @param SkinSlotRenderer $skinSlotRenderer
	 * @param PermissionManager $permissionManager
	 * @return ISkinStructure
	 */
	public static function factory(
		ITemplateDataProvider $templateDataProvider,
		ComponentRenderer $componentRenderer,
		SkinSlotRenderer $skinSlotRenderer,
		PermissionManager $permissionManager
	) {
		$hookContainer = MediaWikiServices::getInstance()->getHookContainer();
		return new static(
			$templateDataProvider, $componentRenderer, $skinSlotRenderer, $permissionManager, $hookContainer
		);
	}

	/**
	 *
	 * @return string
	 */
	public function getName(): string {
		return 'footer';
	}

	/**
	 * @return array
	 */
	public function getParams(): array {
		return [
			'footerlinks' => $this->getFooterLinks(),
			'icons' => $this->getFooterIcons()
		];
	}

	/**
	 * @return string
	 */
	private function getFooterLinks(): string {
		/**
		 * For compatibility the services are not injected
		 * TODO: Inject services
		 */

		/** @var TitleFactory */
		$titleFactory = $this->services->getTitleFactory();
		/** @var RevisionStore */
		$revisionStore = $this->services->getRevisionStore();
		/** @var UrlUtils */
		$urlUtils = $this->services->getUrlUtils();
		/** @var LinkFormatter */
		$linkFormatter = $this->services->getService( 'MWStakeLinkFormatter' );
		/** @var ParserFactory */
		$parserFactory = $this->services->getService( 'MWStakeWikitextParserFactory' );

		$component = new FooterLinksListItems(
			$this->template->getSkin(), $titleFactory, $revisionStore, $urlUtils,
			$linkFormatter, $parserFactory, $this->hookContainer
		);
		$html = $this->componentRenderer->getComponentHtml( $component, $this->componentProcessData );

		return $html;
	}

	/**
	 * @return array
	 */
	private function getFooterIcons(): array {
		$items = [];
		$footericons = $this->template->get( 'footericons' );
		$items = $footericons['poweredby'];
		$urlUtils = $this->services->getUrlUtils();

		foreach ( $items as $key => &$item ) {
			$validHref = isset( $item['url'] )
				&& ( $item['url'] !== '' )
				&& ( strpos( $item['url'], '#' ) !== 0 );

			if ( $validHref ) {
				$parsedURL = $urlUtils->parse( $item['url'] );
				if ( $parsedURL ) {
					$item['target'] = '_blank';
				}
			}
		}
		return $items;
	}

	/**
	 * @param BaseTemplate $baseTemplate
	 * @return void
	 */
	public function setBaseTemplate( BaseTemplate $baseTemplate ): void {
		$this->template = $baseTemplate;
	}

	/**
	 * @return array
	 */
	public function getStyles(): array {
		return [ 'skin.discovery.footer.styles' ];
	}
}
