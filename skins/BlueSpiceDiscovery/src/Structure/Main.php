<?php

namespace BlueSpice\Discovery\Structure;

use BaseTemplate;
use BlueSpice\Discovery\BackLinkProviderFactory;
use BlueSpice\Discovery\Component\BackTo;
use BlueSpice\Discovery\Component\FullscreenButton;
use BlueSpice\Discovery\Component\LastEditInfo;
use BlueSpice\Discovery\Component\TitleActionEdit;
use BlueSpice\Discovery\Component\TitleActionLanguage;
use BlueSpice\Discovery\CookieHandler;
use BlueSpice\Discovery\IBaseTemplateAware;
use BlueSpice\Discovery\IContextSourceAware;
use BlueSpice\Discovery\IResourceProvider;
use BlueSpice\Discovery\ISkinStructure;
use BlueSpice\Discovery\ITemplateDataProvider;
use BlueSpice\Discovery\ITemplateProvider;
use BlueSpice\Discovery\Renderer\ComponentRenderer;
use BlueSpice\Discovery\Renderer\SkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\BreadcrumbSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\DataAfterTitleSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\DataBeforeContentSkinSlotRenderer;
use BlueSpice\Discovery\SkinSlotRenderer\TitleActionsSkinSlotRenderer;
use BlueSpice\Discovery\SubTitleProcessor;
use MediaWiki\Context\IContextSource;
use MediaWiki\Html\Html;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Revision\RevisionStore;
use MWStake\MediaWiki\Component\CommonUserInterface\HtmlIdRegistry;
use Wikimedia\ObjectFactory\ObjectFactory;

class Main implements
	ISkinStructure,
	IBaseTemplateAware,
	IContextSourceAware,
	IResourceProvider,
	ITemplateProvider
{

	/**
	 * @var ComponentRenderer
	 */
	protected $componentRenderer = null;

	/**
	 * @var SkinSlotRenderer
	 */
	protected $skinSlotRenderer = null;

	/**
	 * @var PermissionManager
	 */
	protected $permissionManager = null;

	/**
	 *
	 * @var CookieHandler
	 */
	protected $cookieHandler = null;

	/**
	 * @var LinkRenderer
	 */
	protected $linkRenderer = null;

	/**
	 * @var RevisionStore
	 */
	protected $revisionStore = null;

	/**
	 * @var ObjectFactory
	 */
	protected $objectFactory = null;

	/**
	 * @var BackLinkProviderFactory
	 */
	protected $backLinkProviderFactory = null;

	/**
	 * @var array
	 */
	protected $componentProcessData = [];

	/**
	 * @var BaseTemplate
	 */
	protected $template = null;

	/**
	 * @var IContextSource
	 */
	protected $context = null;

	/**
	 * @var array
	 */
	protected $skinComponents = [];

	/**
	 *
	 * @param ITemplateDataProvider $templateDataProvider
	 * @param PermissionManager $permissionManager
	 * @param LinkRenderer $linkRenderer
	 * @param RevisionStore $revisionStore
	 * @param ObjectFactory $objectFactory
	 * @param BackLinkProviderFactory $backLinkProviderFactory
	 */
	public function __construct(
		ITemplateDataProvider $templateDataProvider,
		ComponentRenderer $componentRenderer,
		SkinSlotRenderer $skinSlotRenderer,
		CookieHandler $cookieHandler,
		PermissionManager $permissionManager,
		LinkRenderer $linkRenderer,
		RevisionStore $revisionStore,
		ObjectFactory $objectFactory,
		BackLinkProviderFactory $backLinkProviderFactory ) {
			$this->componentProcessData = $templateDataProvider->getAll();
			$this->componentRenderer = $componentRenderer;
			$this->skinSlotRenderer = $skinSlotRenderer;
			$this->cookieHandler = $cookieHandler;
			$this->permissionManager = $permissionManager;
			$this->linkRenderer = $linkRenderer;
			$this->revisionStore = $revisionStore;
			$this->objectFactory = $objectFactory;
			$this->backLinkProviderFactory = $backLinkProviderFactory;
	}

	/**
	 *
	 * @param ITemplateDataProvider $templateDataProvider
	 * @param PermissionManager $permissionManager
	 * @param LinkRenderer $linkRenderer
	 * @param RevisionStore $revisionStore
	 * @param ObjectFactory $objectFactory
	 * @param BackLinkProviderFactory $backLinkProviderFactory
	 * @return ISkinStructure
	 */
	public static function factory(
		ITemplateDataProvider $templateDataProvider,
		ComponentRenderer $componentRenderer,
		SkinSlotRenderer $skinSlotRenderer,
		CookieHandler $cookieHandler,
		PermissionManager $permissionManager,
		LinkRenderer $linkRenderer,
		RevisionStore $revisionStore,
		ObjectFactory $objectFactory,
		BackLinkProviderFactory $backLinkProviderFactory ) {
		return new static(
			$templateDataProvider, $componentRenderer, $skinSlotRenderer, $cookieHandler,
			$permissionManager, $linkRenderer, $revisionStore, $objectFactory, $backLinkProviderFactory );
	}

	/**
	 *
	 * @return string
	 */
	public function getName(): string {
		return 'main';
	}

	/**
	 * @return string
	 */
	public function getTemplatePath(): string {
		return $GLOBALS['wgStyleDirectory'] .
			'/BlueSpiceDiscovery/resources/templates/structure';
	}

	/**
	 * @return string
	 */
	public function getTemplateName(): string {
		return $this->getName();
	}

	/**
	 * Parse templates recursive
	 *
	 * @return bool
	 */
	public function enableRecursivePartials(): bool {
		return false;
	}

	/**
	 * @return array
	 */
	public function getParams(): array {
		$subTitleProcessor = new SubTitleProcessor();
		$subTitleProcessor->parse( $this->template->get( 'subtitle' ) );

		$this->fetchSiteNotice();
		$this->fetchSkinSlotDataBeforeContent();
		$this->fetchBreadcrumb();
		$this->fetchLastEdit();
		$this->fetchTitle();
		$this->fetchSkinSlotTitleActions();
		$this->fetchTitleActionEdit();
		$this->fetchTitleActionLanguage();
		$this->fetchTitleActionFullscreenButton();
		$this->fetchBackTo();
		$this->fetchRedirect( $subTitleProcessor->get( 'redirect' ) );
		$this->fetchSkinSlotDataAfterTitle();
		$this->fetchUndelete();
		$this->fetchIndicators();
		$this->fetchSubcontent( $subTitleProcessor->get() );
		$this->fetchBodyText();

		return $this->skinComponents;
	}

	/**
	 * https://www.mediawiki.org/wiki/Manual:Skinning_Part_2#Site_notice
	 *
	 * @return void
	 */
	private function fetchSiteNotice() {
		$this->skinComponents['sitenotice'] = $this->template->get( 'sitenotice' );
	}

	/**
	 *
	 * @return void
	 */
	private function fetchBreadcrumb() {
		$html = $this->skinSlotRenderer->getSkinSlotHtml(
			BreadcrumbSkinSlotRenderer::REG_KEY,
			$this->componentProcessData
		);

		$this->skinComponents['breadcrumb-nav'] = $html;
	}

	/**
	 *
	 * @return void
	 */
	private function fetchLastEdit() {
		$component = new LastEditInfo(
			$this->context, $this->linkRenderer, $this->revisionStore, $this->objectFactory
		);
		$html = $this->componentRenderer->getComponentHtml( $component, $this->componentProcessData );

		$this->skinComponents['last-edit'] = $html;
	}

	/**
	 *
	 * @return void
	 */
	private function fetchTitleActionEdit() {
		$component = new TitleActionEdit( $this->permissionManager, $this->componentProcessData, $this->objectFactory );
		$html = $this->componentRenderer->getComponentHtml( $component, $this->componentProcessData );

		$this->skinComponents['title-action-edit'] = $html;
	}

	/**
	 * @return void
	 */
	private function fetchTitleActionLanguage() {
		$component = new TitleActionLanguage( $this->componentProcessData );
		$html = $this->componentRenderer->getComponentHtml( $component, $this->componentProcessData );

		$this->skinComponents['title-action-language'] = $html;
	}

	/**
	 *
	 * @return void
	 */
	private function fetchTitleActionFullscreenButton() {
		$component = new FullscreenButton( $this->cookieHandler );
		$html = $this->componentRenderer->getComponentHtml( $component, $this->componentProcessData );

		$this->skinComponents['fullscreen-button'] = $html;
	}

	/**
	 * https://www.mediawiki.org/wiki/Manual:Skinning_Part_2#Titel
	 *
	 * @return void
	 */
	private function fetchTitle() {
		$title = $this->template->getSkin()->getTitle();
		$regularTitle = $title->getPrefixedText();

		// data['title'] contains either the DISPLAYTITLE title or the page title.
		// The page title could be wrapped into some html but the DISPLAYTITLE not.
		$displayTitle = $this->template->data['title'];

		// Check if $displayTitle contains DISPLAYTITLE or page title
		if ( $regularTitle === strip_tags( $displayTitle ) ) {
			// $displayTitle contains page title.
			// But we only want to show the subpage text in title section.

			$subpageText = $title->getSubpageText();

			// Check if $displayTitle is wrapped into html
			if ( $regularTitle === $displayTitle ) {
				// $displayTitle is not wrapped into html
				$displayTitle = $subpageText;
			} else {
				// $displayTitle is wrapped into html. We want to keep this wrapper.
				// But we don't want to show namespace part or separator part.
				$matches = [];
				$regEx = '.*?(<span\s*?class="mw-page-title-main"\s*?>)(.*)(</span>).*$';
				$status = preg_match( '#' . $regEx . '#', $displayTitle, $matches );

				if ( $status ) {
					$displayTitle = $matches[1] . $subpageText . $matches[3];
				}
			}
		}

		$this->skinComponents['title'] = $displayTitle;
	}

	/**
	 *
	 * @return void
	 */
	private function fetchBackTo() {
		$backTo = new BackTo( $this->backLinkProviderFactory );
		$html = $this->componentRenderer->getComponentHtml( $backTo, $this->componentProcessData );
		$this->skinComponents['backTo'] = $html;
	}

	/**
	 * Besides the tagline MediaWiki has two subtitles below the title to take into account.
	 * This one is used for various things like the subpage hierarchy and redirected from line.
	 * https://www.mediawiki.org/wiki/Manual:Skinning_Part_2#Subtitles
	 * @param string $redirect
	 * @return void
	 */
	private function fetchRedirect( $redirect ) {
		// We only want the redirect here
		$this->skinComponents['redirect'] = $redirect;
	}

	/**
	 * Besides the tagline MediaWiki has two subtitles below the title to take into account.
	 * This one is specifically for the undelete message.
	 * https://www.mediawiki.org/wiki/Manual:Skinning_Part_2#Subtitles
	 *
	 * @return void
	 */
	private function fetchUndelete() {
		$this->skinComponents['undelete'] = $this->template->get( 'undelete' );
	}

	/**
	 * https://www.mediawiki.org/wiki/Manual:Skinning_Part_2#Page_status_indicators
	 *
	 * @return void
	 */
	private function fetchIndicators() {
		$indicators = $this->template->get( 'indicators' );
		if ( !empty( $indicators ) ) {
			if ( isset( $indicators[ 'mw-helplink' ] ) ) {
				unset( $indicators[ 'mw-helplink' ] );
			}
			$htmlIdRegistry = new HtmlIdRegistry();
			$indicatorHtml = '';
			foreach ( $indicators as $id => $content ) {
				$indicatorHtml .= Html::openElement(
					'div',
					[
						'id' => $htmlIdRegistry->makeHtmlIdSafe( "mw-indicator-$id" ),
						'class' => 'mw-indicator'
					]
				);
				$indicatorHtml .= $content;
				$indicatorHtml .= Html::closeElement( 'div' );
			}
			$this->skinComponents['indicators'] = $indicatorHtml;
		}
	}

	/**
	 * https://www.mediawiki.org/wiki/Manual:Skinning_Part_2#Subtitles
	 * @param string $subcontent
	 * @return void
	 */
	private function fetchSubcontent( $subcontent ) {
		$this->skinComponents['html-subtitle'] = $subcontent;
	}

	/**
	 * https://www.mediawiki.org/wiki/Manual:Skinning_Part_2#Body_text
	 *
	 * @return void
	 */
	private function fetchBodyText() {
		$this->skinComponents['bodytext'] = $this->template->get( 'bodytext' );
	}

	/**
	 *
	 * @return void
	 */
	private function fetchSkinSlotDataBeforeContent() {
		$html = $this->skinSlotRenderer->getSkinSlotHtml(
			DataBeforeContentSkinSlotRenderer::REG_KEY,
			$this->componentProcessData
		);

		$this->skinComponents['skin-slot-data-before-content'] = $html;
	}

	/**
	 *
	 * @return void
	 */
	private function fetchSkinSlotDataAfterTitle() {
		$html = $this->skinSlotRenderer->getSkinSlotHtml(
			DataAfterTitleSkinSlotRenderer::REG_KEY,
			$this->componentProcessData
		);

		$this->skinComponents['skin-slot-data-after-title'] = $html;
	}

	/**
	 *
	 * @return void
	 */
	private function fetchSkinSlotTitleActions() {
		$html = $this->skinSlotRenderer->getSkinSlotHtml(
			TitleActionsSkinSlotRenderer::REG_KEY,
			$this->componentProcessData
		);

		$this->skinComponents['skin-slot-title-actions'] = $html;
	}

	/**
	 * @param BaseTemplate $baseTemplate
	 * @return void
	 */
	public function setBaseTemplate( BaseTemplate $baseTemplate ): void {
		$this->template = $baseTemplate;
	}

	/**
	 * @param IContextSource $context
	 * @return void
	 */
	public function setContextSource( IContextSource $context ): void {
		$this->context = $context;
	}

	/**
	 * @return array
	 */
	public function getStyles(): array {
		return [ 'skin.discovery.main.styles' ];
	}

	/**
	 * @return array
	 */
	public function getScripts(): array {
		return [];
	}

	/**
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool {
		return true;
	}
}
