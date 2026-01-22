<?php

namespace BlueSpice\Discovery\BreadcrumbDataProvider;

use Exception;
use MediaWiki\Language\RawMessage;
use MediaWiki\SpecialPage\SpecialPageFactory;
use MediaWiki\Title\Title;

class SpecialEditWatchlistProvider extends BaseBreadcrumbDataProvider {

	private string $actionName;

	/**
	 * @param SpecialPageFactory $specialPageFactory
	 * @param TitleFactory $titleFactory
	 * @param MessageLocalizer $messageLocalizer
	 * @param WebRequestValues $webRequestValues
	 * @param NamespaceInfo $namespaceInfo
	 */
	public function __construct( private SpecialPageFactory $specialPageFactory,
		$titleFactory, $messageLocalizer, $webRequestValues, $namespaceInfo ) {
		parent::__construct( $titleFactory, $messageLocalizer, $webRequestValues, $namespaceInfo );
		$this->actionName = '';
	}

	/**
	 * @param Title $title
	 * @return Title
	 */
	public function getRelevantTitle( $title ): Title {
		$specialPage = $this->specialPageFactory->getPage( 'EditWatchlist' );
		if ( !$specialPage ) {
			throw new Exception( 'The "EditWatchlist" page doesn\'t exist' );
		}
		$specialPageTitle = $specialPage->getPageTitle();
		if ( !isset( $this->webRequestValues['title'] ) ) {
			return $specialPageTitle;
		}

		$requestTitle = $this->webRequestValues['title'];
		$bits = explode( '/', $requestTitle );
		if ( count( $bits ) === 1 ) {
			return $specialPageTitle;
		}
		$this->actionName = array_pop( $bits );
		return $specialPageTitle;
	}

	/**
	 * @param Title $title
	 * @return array
	 */
	public function getLabels( $title ): array {
		if ( !$this->actionName ) {
			return [];
		}
		$msgSpecialKey = 'bs-discovery-breadcrumb-label-special-editwatchlist-' . strtolower( $this->actionName );
		$msgSpecialText = $this->messageLocalizer->msg( $msgSpecialKey );
		if ( !$msgSpecialText->exists() ) {
			$msgSpecialText = new RawMessage( $this->actionName );
		}
		$labels[] = [
			'text' => $msgSpecialText
		];

		return $labels;
	}

	/**
	 *
	 * @param Title $title
	 * @return bool
	 */
	public function applies( Title $title ): bool {
		return $title->isSpecial( 'EditWatchlist' );
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function isSelfLink( $node ): bool {
		if ( $this->actionName ) {
			return false;
		}
		return true;
	}
}
