<?php

namespace BlueSpice\Discovery\Component;

use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Page\PageProps;
use MediaWiki\Title\NamespaceInfo;
use MediaWiki\Title\Title;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCard;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardHeader;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleLinklistGroupFromArray;
use MWStake\MediaWiki\Component\CommonUserInterface\LinkFormatter;

class NamespaceMainPages extends SimpleCard {

	/** @var PageProps */
	private $pageProps = null;

	/** @var LinkFormatter */
	private LinkFormatter $linkFormatter;

	/** @var NamespaceInfo */
	private NamespaceInfo $namespaceInfo;

	/**
	 * @param PageProps $pageProps
	 */
	public function __construct( PageProps $pageProps ) {
		$this->pageProps = $pageProps;
		parent::__construct( [] );

		$services = MediaWikiServices::getInstance();
		$this->linkFormatter = $services->getService( 'MWStakeLinkFormatter' );
		$this->namespaceInfo = $services->getNamespaceInfo();
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return 'namespace-mainpage-links';
	}

	/**
	 * @inheritDoc
	 */
	public function getContainerClasses(): array {
		return [
			'w-100',
			'bg-transp'
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getSubComponents(): array {
		return $this->buildPanels();
	}

	/**
	 *
	 * @param IContextSource $context
	 *
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool {
		return true;
	}

	/**
	 * @return array
	 */
	private function buildPanels(): array {
		$items = [];

		$context = RequestContext::getMain();
		$config = $context->getSkin()->getConfig();
		$namespaces = $config->get( 'ContentNamespaces' );

		$mainpage = Title::newMainPage();
		$mainPageText = $mainpage->getText();
		$mainpages = [];
		foreach ( $namespaces as $namespace ) {
			$title = Title::makeTitleSafe( $namespace, $mainPageText );
			if ( !$title->exists() ) {
				continue;
			}

			$nsText = $this->getNamespaceText( $title, $namespace );

			$mainpages[$nsText] = [
				'text' => $nsText,
				'title' => $title->getPrefixedText(),
				'href' => $title->getLinkURL()
			];

			if ( $context->getTitle()->equals( $title ) ) {
				$mainpages[$nsText]['class'] = 'active';
			}
		}

		ksort( $mainpages );

		$id = 'namespace-mainpage-links-pnl';
		$headerText = wfMessage( 'bs-discovery-namespace-mainpage-links-pnl-header-text' )->text();

		$items[] = new SimpleCard( [
			'id' => $id,
			'classes' => [
				'w-100',
				'bg-transp'
			],
			'items' => [
				new SimpleCardHeader( [
					'id' => $id . '-head',
					'classes' => [ 'menu-title' ],
					'items' => [
						new Literal(
							$id . '-head', $headerText
						)
					]
				] ),
				new SimpleLinklistGroupFromArray( [
					'id' => $id,
					'classes' => [],
					'aria' => [
						'labelledby' => $id . '-head'
					],
					'links' => $this->linkFormatter->formatLinks( $mainpages ),
					'role' => 'group',
					'item-role' => 'presentation'
				] )
			]
		] );

		return $items;
	}

	/**
	 * Which text is used hirarchy:
	 *  1. special case: NS_MAIN
	 *  2. from message key
	 *  3. displaytitle
	 *  4. alias
	 *  5. canonical name
	 *  6. page title
	 *
	 * @param Title $title
	 * @param int $namespace
	 *
	 * @return string
	 */
	private function getNamespaceText( Title $title, int $namespace ): string {
		if ( $namespace === NS_MAIN ) {
			$nsMsg = 'ns-main-label';

			if ( wfMessage( $nsMsg )->exists() ) {
				return wfMessage( $nsMsg )->text();
			}
		}

		$nsText = null;

		$pageProps = $this->pageProps->getAllProperties( $title );
		$pageProps = $pageProps[$title->getArticleID()] ?? [];

		if ( isset( $pageProps['displaytitle'] ) ) {
			$nsText = $pageProps['displaytitle'];
		}

		if ( !$nsText ) {
			global $wgNamespaceAliases;
			if ( $wgNamespaceAliases ) {
				$nsAliases = array_flip( $wgNamespaceAliases );

				if ( isset( $nsAliases[$namespace] ) ) {
					$nsText = str_replace( '_', ' ', $nsAliases[$namespace] );
				}
			}
		}

		if ( !$nsText ) {
			$canonicalName = $this->namespaceInfo->getCanonicalName( $namespace );

			if ( $canonicalName ) {
				$nsText = str_replace( '_', ' ', $canonicalName );
			}
		}

		if ( !$nsText ) {
			$nsText = $title->getText();
		}

		$nsMsg = 'ns-' . strtolower( $nsText ) . '-label';
		if ( wfMessage( $nsMsg )->exists() ) {
			return wfMessage( $nsMsg )->text();
		}

		return $nsText;
	}
}
