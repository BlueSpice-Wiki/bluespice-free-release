<?php

namespace BlueSpice\Discovery\Component;

use BlueSpice\Discovery\HookRunner;
use Exception;
use MediaWiki\Context\IContextSource;
use MediaWiki\Extension\MenuEditor\Node\TwoFoldLinkSpec;
use MediaWiki\Extension\MenuEditor\Parser\WikitextMenuParser;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Html\Html;
use MediaWiki\Message\Message;
use MediaWiki\Revision\RevisionStore;
use MediaWiki\Skin\SkinComponentLink;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\Utils\UrlUtils;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;
use MWStake\MediaWiki\Component\CommonUserInterface\LinkFormatter;
use MWStake\MediaWiki\Component\Wikitext\ParserFactory;
use MWStake\MediaWiki\Lib\Nodes\INode;
use Skin;

class FooterLinksListItems extends Literal {

	/** @var Skin */
	private $skin;

	/** @var Title */
	private $footerLinksSourceTitle;

	/** @var TitleFactory */
	private $titleFactory;

	/** @var UrlUtils */
	private $urlUtils;

	/** @var LinkFormatter */
	private $linkFormatter;

	/** @var ParserFactory */
	private $parserFactory;

	/** @var RevisionStore */
	private $revisionStore;

	/** @var HookContainer */
	private $hookContainer;

	/**
	 * @param Skin $skin
	 * @param TitleFactory $titleFactory
	 * @param RevisionStore $revisionStore
	 * @param UrlUtils $urlUtils
	 * @param LinkFormatter $linkFormatter
	 * @param ParserFactory $parserFactory
	 * @param HookContainer $hookContainer
	 * v
	 */
	public function __construct(
		Skin $skin, TitleFactory $titleFactory, RevisionStore $revisionStore, UrlUtils $urlUtils,
		LinkFormatter $linkFormatter, ParserFactory $parserFactory, HookContainer $hookContainer
	) {
		parent::__construct( '', '' );

		$this->skin = $skin;
		$this->titleFactory = $titleFactory;
		$this->revisionStore = $revisionStore;
		$this->urlUtils = $urlUtils;
		$this->linkFormatter = $linkFormatter;
		$this->parserFactory = $parserFactory;
		$this->hookContainer = $hookContainer;
		$this->footerLinksSourceTitle = $this->titleFactory->makeTitle( NS_MEDIAWIKI, 'FooterLinks' );
	}

	/** @inheritDoc */
	public function getId(): string {
		return 'footerlinks';
	}

	/** @inheritDoc */
	public function shouldRender( IContextSource $context ): bool {
		return $context->getAuthority()->isAllowed( 'read' );
	}

	/** @inheritDoc */
	public function getHtml(): string {
		$footerLinks = [];

		if ( $this->footerLinksSourceTitle->exists() ) {
			// The key here is 'places' to be MediaWiki compatible.
			$footerLinks['places'] = $this->getCustomFooterLinks();
		} else {
			$footerLinks['places'] = $this->getDefaultFooterLinks();

			$footerLinks['places']['imprint'] = $this->buildFooterLink(
				'imprint',
				'bs-discovery-footerlinks-imprint-link-desc',
				'bs-discovery-footerlinks-imprint-link-page'
			);
			$footerLinks['places']['termsofservice'] = $this->buildFooterLink(
				'termsofservice',
				'bs-discovery-footerlinks-termsofservice-link-desc',
				'bs-discovery-footerlinks-termsofservice-link-page'
			);
		}

		foreach ( $footerLinks as $key => $existingItems ) {
			$newItems = [];
			$this->getHookRunner()->onSkinAddFooterLinks( $this->skin, $key, $newItems );
			$footerLinks[$key] = $existingItems + $newItems;
		}

		$this->hookContainer->run( 'BlueSpiceDiscoveryAfterGetFooterPlaces', [ &$footerLinks['places'] ] );

		$items = [];
		foreach ( $footerLinks['places'] as $link ) {
			$items[] = Html::rawElement( 'li', [], $link );
		}

		$authority = $this->skin->getContext()->getAuthority();
		if ( $authority->isAllowed( 'editinterface' ) ) {
			$items[] = $this->buildEditLink();
		}

		return implode( '', $items );
	}

	/**
	 * Gets the link to the wiki's privacy policy, about page, and disclaimer page.
	 * Replaces old Skin::getSiteFooterLinks.
	 *
	 * @return string[] Map of (key => HTML) for 'privacy', 'about', 'disclaimer'
	 */
	private function getDefaultFooterLinks(): array {
		$footerLinks = [];

		$linkSpecs = [
			'privacy' => [ 'desc' => 'privacy', 'page' => 'privacypage' ],
			'about' => [ 'desc' => 'aboutsite', 'page' => 'aboutpage' ],
			'disclaimer' => [ 'desc' => 'disclaimers', 'page' => 'disclaimerpage' ],
		];

		foreach ( $linkSpecs as $key => [ 'desc' => $descKey, 'page' => $pageKey ] ) {
			$footerLinks[$key] = $this->buildFooterLink( $key, $descKey, $pageKey );
		}

		return $footerLinks;
	}

	/**
	 * Build a SkinComponentLink based on two message keys (desc, page).
	 * Replaces old Skin::footerLink.
	 *
	 * @param string $key Unique link key
	 * @param string $descKey The i18n message key for the link text
	 * @param string $pageKey The i18n message key for the page to link to
	 * @return string HTML anchor
	 */
	private function buildFooterLink( string $key, string $descKey, string $pageKey ): string {
		$descMsg = Message::newFromKey( $descKey )->inContentLanguage();
		$pageMsg = Message::newFromKey( $pageKey )->inContentLanguage();

		if ( !$descMsg->exists() || !$pageMsg->exists() ) {
			return '';
		}

		$descText = $descMsg->text();
		$pageText = $pageMsg->text();

		$title = Title::newFromText( $pageText );
		if ( !$title ) {
			return '';
		}

		$item = [
			'href' => $title->getLocalURL(),
			'text' => $descText,
			'title' => $title->getText(),
		];

		$link = new SkinComponentLink( $key, $item, $this->skin );

		return $link->getTemplateData()['html'];
	}

	/**
	 * @return array
	 */
	private function getCustomFooterLinks(): array {
		$parserData = $this->getParserData();

		$customFooterLinksData = $this->buildCustomFooterLinksData( $parserData );

		$links = [];
		foreach ( $customFooterLinksData as $data ) {
			$text = $data['text'];
			unset( $data['text'] );

			$links[$text] = Html::element(
				'a',
				$data,
				htmlspecialchars( $text )
			);
		}

		return $links;
	}

	/**
	 * @param INode[] $parserData
	 * @return array
	 */
	private function buildCustomFooterLinksData( array $parserData ): array {
		$links = [];
		foreach ( $parserData as $dataItem ) {
			if ( !( $dataItem instanceof TwoFoldLinkSpec ) ) {
				continue;
			}

			$url = $this->skin->makeInternalOrExternalUrl( $dataItem->getTarget() );
			$data = [
				'href' => $url,
				'text' => $dataItem->getLabel(),
				'title' => $dataItem->getLabel(),
				'role' => 'link'
			];

			$parsedURL = $this->urlUtils->parse( $dataItem->getTarget() );
			if ( !$parsedURL ) {
				$title = $this->titleFactory->newFromText( $dataItem->getTarget() );
				if ( $title && !$title->exists() ) {
					$data['class'] = 'new';
				}
			}

			$links[] = $data;
		}

		if ( empty( $links ) ) {
			return [];
		}

		$formattedLinks = $this->linkFormatter->formatLinks( $links );

		return $formattedLinks;
	}

	/**
	 * @return string
	 */
	private function buildEditLink(): string {
		$link = Html::openElement(
			'li',
			[
				'class' => 'edit-footerlinks-link',
			]
		);

		$params = [
			'role' => 'link',
			'id' => 'edit-footerlinks-link',
			'href' => $this->footerLinksSourceTitle->getEditURL()
		];

		if ( !$this->footerLinksSourceTitle->exists() ) {
			$params['class'] = 'new';
		}

		$link .= Html::element(
			'a',
			$params,
			Message::newFromKey( 'bs-discovery-edit-footerlinks-link-text' )
		);

		$link .= Html::closeElement( 'li' );

		return $link;
	}

	/**
	 * @return INode[]
	 */
	private function getParserData(): array {
		$parser = new WikitextMenuParser(
			$this->revisionStore->getRevisionByTitle( $this->footerLinksSourceTitle ),
			$this->parserFactory->getNodeProcessors()
		);

		if ( !$parser ) {
			return [];
		}

		try {
			return $parser->parse();
		} catch ( Exception $ex ) {
			return [];
		}
	}

	/**
	 * @return HookRunner
	 */
	private function getHookRunner() {
		return new HookRunner( $this->hookContainer );
	}
}
