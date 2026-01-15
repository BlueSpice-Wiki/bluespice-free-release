<?php

namespace BlueSpice\Discovery\BreadcrumbDataProvider;

use BlueSpice\Discovery\IBreadcrumbDataProvider;
use MediaWiki\Context\RequestContext;
use MediaWiki\Language\RawMessage;
use MediaWiki\Title\NamespaceInfo;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MessageLocalizer;

class BaseBreadcrumbDataProvider implements IBreadcrumbDataProvider {

	/**
	 *
	 * @var Title
	 */
	private $relevantTitle = null;

	/**
	 *
	 * @var TitleFactory
	 */
	protected $titleFactory;

	/**
	 *
	 * @var bool
	 */
	protected $talkName = false;

	/**
	 *
	 * @var array
	 */
	protected $webRequestValues;

	/**
	 *
	 * @var MessageLocalizer
	 */
	protected $messageLocalizer;

	/**
	 *
	 * @var NamespaceInfo
	 */
	protected $namespaceInfo = null;

	/**
	 * @param TitleFactory $titleFactory
	 * @param MessageLocalizer $messageLocalizer
	 * @param WebRequestValues $webRequestValues
	 * @param NamespaceInfo $namespaceInfo
	 */
	public function __construct( $titleFactory, $messageLocalizer, $webRequestValues, $namespaceInfo ) {
		$this->titleFactory = $titleFactory;
		$this->messageLocalizer = $messageLocalizer;
		$this->webRequestValues = $webRequestValues;
		$this->namespaceInfo = $namespaceInfo;
	}

	/**
	 * @param Title $title
	 * @return Title
	 */
	public function getRelevantTitle( $title ): Title {
		$this->relevantTitle = $title;
		// `Special:Move/Talk:Some_Page/with/Subpage`
		if ( $this->relevantTitle->isSpecialPage() ) {
			$fullPageTitle = $this->relevantTitle->getPrefixedDBkey();

			// `[ "Special:Move/MyPage", "Talk:Some_Page/with/Subpage" ]`
			$titleParts = explode( '/', $fullPageTitle );
			$this->relevantTitle = $this->titleFactory->newFromText( array_shift( $titleParts ) );
		}

		if ( $this->relevantTitle->isTalkPage() ) {
			$this->talkName = true;
			$titleNamespace = $this->namespaceInfo->getSubjectPage( $this->relevantTitle );

			$this->relevantTitle = $this->titleFactory->makeTitle(
				$titleNamespace->getNamespace(),
				$titleNamespace->getDBkey()
			);
		}
		return $this->relevantTitle;
	}

	/**
	 * @param Title $title
	 * @return array
	 */
	public function getNodes( Title $title ): array {
		$nodeTitleParts = explode( '/', $title->getPrefixedText() );
		$numberOfParts = count( $nodeTitleParts );
		$nodeTitleName = '';
		$nodes = [];

		for ( $index = 0; $index < $numberOfParts; $index++ ) {
			if ( empty( $nodeTitleParts[$index] ) ) {
				continue;
			}
			$node = [];
			$nodeTitleName .= '/' . $nodeTitleParts[$index];

			$nodeTitle = Title::newFromText(
				trim( $nodeTitleName, '/' ),
				$title->getNamespace()
			);
			$path = '';
			if ( $title->getNamespace() === 0 ) {
				$path = ':';
			}
			$path .= $nodeTitle->getPrefixedDBkey();

			$classes = [];
			$current = false;
			$subpages = true;
			$btnClass = '';

			if ( !$nodeTitle->exists() && !$nodeTitle->isSpecialPage() ) {
				$classes[] = 'new';
			}

			if ( $nodeTitle->equals( $title ) || $index === ( $numberOfParts - 1 ) ) {
				// See https://getbootstrap.com/docs/5.0/components/breadcrumb/
				$current = true;
				$classes[] = 'active';

				// We assume that _all_ nodes have subpages, as also non existing ones will be listed
				// Only the leaf node must be checked explicitly
				if ( $nodeTitle->hasSubpages() ) {
					$subpages = true;
				} else {
					$subpages = false;
					$btnClass = 'd-none';
				}
			}

			if ( strpos( $nodeTitleParts[$index], ':' ) &&
					$nodeTitle->isSpecialPage() ) {
				$text = explode( ':', $nodeTitleParts[$index], 2 );
				$nodeText = $text[1];
			} else {
				$nodeText = $nodeTitleParts[$index];
			}

			$node = [
				'id' => $nodeTitle->getArticleID(),
				'nodeText' => trim( $nodeText ),
				'url' => $nodeTitle->getLocalURL(),
				'classes' => $classes,
				'title' => $nodeTitle->getFullText(),
				'splitBtnClass' => $btnClass,
				'subpages' => $subpages,
				'path' => $path,
				'current' => $current
			];
			array_push( $nodes, $node );
		}
		return $nodes;
	}

	/**
	 * @param Title $title
	 * @return array
	 */
	public function getLabels( $title ): array {
		$labels = [];
		if ( $this->talkName === true ) {
			$labels[] = [
				'text' => $this->messageLocalizer->msg( 'bs-discovery-breadcrumb-label-talk' )
			];
		}
		if ( isset( $this->webRequestValues['action'] ) ) {
			$msgKey = 'bs-discovery-breadcrumb-label-action-' . $this->webRequestValues['action'];
			$msgText = $this->messageLocalizer->msg( $msgKey );
			if ( !$msgText->exists() ) {
				$msgText = new RawMessage( $this->webRequestValues['action'] );
			}
			$labels[] = [ 'text' => $msgText ];
		}
		return $labels;
	}

	/**
	 *
	 * @param Title $title
	 * @return bool
	 */
	public function applies( Title $title ): bool {
		return true;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function isSelfLink( $node ): bool {
		$requestContext = RequestContext::getMain();
		$action = $requestContext->getRequest()->getVal( 'action', 'view' );
		if ( isset( $node['current'] ) && $node['current'] === true && $action === 'view' ) {
			return true;
		}
		return false;
	}
}
