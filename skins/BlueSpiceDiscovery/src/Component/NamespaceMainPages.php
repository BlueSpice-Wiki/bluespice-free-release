<?php

namespace BlueSpice\Discovery\Component;

use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Page\PageProps;
use MediaWiki\Title\Title;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCard;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleCardHeader;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleLinklistGroupFromArray;
use MWStake\MediaWiki\Component\CommonUserInterface\LinkFormatter;

class NamespaceMainPages extends SimpleCard {

	/** @var PageProps */
	private $pageProps = null;

	/**
	 * @param PageProps $pageProps
	 */
	public function __construct( PageProps $pageProps ) {
		$this->pageProps = $pageProps;
		parent::__construct( [] );
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
		return [ 'w-100', 'bg-transp' ];
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
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool {
		return true;
	}

	/**
	 *
	 * @return array
	 */
	private function buildPanels(): array {
		$items = [];

		$services = MediaWikiServices::getInstance();
		/** @var LinkFormatter */
		$linkFormatter = $services->getService( 'MWStakeLinkFormatter' );
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

			$nsText = $title->getNsText();
			if ( $namespace === NS_MAIN ) {
				$nsText = 'main';
			}

			$pageProps = $this->pageProps->getAllProperties( $title );
			$pageProps = $pageProps[$title->getArticleID()] ?? [];
			if ( isset( $pageProps['displaytitle'] ) ) {
				$nsText = $pageProps['displaytitle'];
			}

			$nsMsg = 'ns-' . strtolower( $nsText ) . '-label';
			if ( wfMessage( $nsMsg )->exists() ) {
				$nsText = wfMessage( $nsMsg )->text();
			}

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
			'classes' => [ 'w-100', 'bg-transp' ],
			'items' => [
				new SimpleCardHeader( [
					'id' => $id . '-head',
					'classes' => [ 'menu-title' ],
					'items' => [
						new Literal(
							$id . '-head',
							$headerText
						)
					]
				] ),
				new SimpleLinklistGroupFromArray( [
					'id' => $id,
					'classes' => [],
					'aria' => [
						'labelledby' => $id . '-head'
					],
					'links' => $linkFormatter->formatLinks( $mainpages ),
					'role' => 'group',
					'item-role' => 'presentation'
				] )
			]
		] );

		return $items;
	}
}
