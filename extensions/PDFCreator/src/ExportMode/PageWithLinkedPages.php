<?php

namespace MediaWiki\Extension\PDFCreator\ExportMode;

use DOMDocument;
use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;
use MediaWiki\Extension\PDFCreator\IContextSourceAware;
use MediaWiki\Extension\PDFCreator\Utility\UrlHelper;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\RevisionRenderer;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;

class PageWithLinkedPages extends Page implements IContextSourceAware {

	/** @var IContextSource */
	private $context;

	/** @var RevisionLookup */
	private $revisionLookup;

	/** @var RevisionRenderer */
	private $revisionRenderer;

	/** @var PermissionManager */
	private $permissionManager;

	/**
	 * @param Config $config
	 * @param TitleFactory $titleFactory
	 * @param RevisionLookup $revisionLookup
	 * @param RevisionRenderer $revisionRenderer
	 * @param PermissionManager $permissionManager
	 */
	public function __construct(
		Config $config, TitleFactory $titleFactory,
		RevisionLookup $revisionLookup, RevisionRenderer $revisionRenderer,
		PermissionManager $permissionManager
	) {
		parent::__construct( $config, $titleFactory );
		$this->revisionLookup = $revisionLookup;
		$this->revisionRenderer = $revisionRenderer;
		$this->permissionManager = $permissionManager;
	}

	/**
	 * @param IContextSource $context
	 * @return void
	 */
	public function setContext( IContextSource $context ): void {
		$this->context = $context;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getKey(): string {
		return 'pageWithLinkedPages';
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getLabel(): string {
		return 'pdfcreator-export-plugin-mode-option-recursive-label';
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function applies( $format ): bool {
		return ( $format === $this->getKey() ) ? true : false;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getExportPages( $title, $data ): array {
		$revisionId = $title->getLatestRevID();
		if ( isset( $data['revId'] ) ) {
			$revisionId = $data['revId'];
		}

		$revisionRecord = $this->revisionLookup->getRevisionByTitle( $title, $revisionId );
		if ( !$revisionRecord ) {
			// Fallback is spec contains wrong revision id
			$revisionId = $title->getLatestRevID();
			$revisionRecord = $this->revisionLookup->getRevisionByTitle( $title, $revisionId );
		}

		$params = $data;
		$params['rev-id'] = $revisionId;
		if ( isset( $params['revId'] ) ) {
			unset( $params['revId'] );
		}
		if ( isset( $data['revId'] ) ) {
			unset( $data['revId'] );
		}

		$pages[] = [
			'type' => 'page',
			'target' => $title->getPrefixedDBkey(),
			'params' => $params
		];

		if ( $revisionRecord ) {
			$dom = $this->getPageContentDOM( $revisionRecord );
		} else {
			return $pages;
		}

		if ( $dom instanceof DOMDocument === false ) {
			return $pages;
		}

		$linkedPages = $this->getLinkedPages( $dom, $this->context );

		$pages = array_merge( $pages, $linkedPages );

		return $pages;
	}

	/**
	 * @param RevisionRecord $revisionRecord
	 * @return DOMDocument
	 */
	private function getPageContentDOM( RevisionRecord $revisionRecord ): DOMDocument {
		$user = $this->context->getUser();
		$options = ParserOptions::newFromContext( $this->context );
		$options->setSuppressSectionEditLinks();

		$renderedRevision = $this->revisionRenderer->getRenderedRevision( $revisionRecord, $options, $user );
		$output = $renderedRevision->getRevisionParserOutput();

		$dom = new DOMDocument();
		// ParserOutput->getText() is deprecated and the replacement is
		// runOutputPipeline which returns a ParserOutput as well
		// so its required to get text with getContentHolderText which
		// is currently marked as unstable but on many different places its used
		// as well like EditPage in mediawiki core but also in other extensions -
		// thats why we decided to use it as well for now
		$text = $output->runOutputPipeline( $options )->getContentHolderText();
		$htmlText = "<!DOCTYPE html><html><head><meta charset=\"UTF-8\"></head><body>" . $text . "</body></html>";
		$dom->loadHTML( $htmlText );

		return $dom;
	}

	/**
	 * @param DOMDocument $dom
	 * @return array
	 */
	private function getLinkedPages( DOMDocument $dom ): array {
		$linkedTitles = [];

		$excludeClasses = [ 'new', 'external', 'media' ];

		$links = $dom->getElementsByTagName( 'a' );
		foreach ( $links as $link ) {
			if ( !$link->hasAttribute( 'href' ) ) {
				continue;
			}
			$href = $link->hasAttribute( 'href' );
			if ( strpos( $href, '#' ) === 0 || strpos( $href, 'javascript:' ) === 0 ) {
				continue;
			}

			$class = $link->getAttribute( 'class' );
			$classes = explode( ' ', $class );

			// HINT: http://stackoverflow.com/questions/7542694/in-array-multiple-values
			if ( count( array_intersect( $classes, $excludeClasses ) ) > 0 ) {
				continue;
			}

			$title = $this->getTitleFromUrl( $link->getAttribute( 'href' ) );
			if ( $title == null || !$title->canExist() ) {
				continue;
			}

			// Avoid double export
			if ( in_array( $title->getPrefixedText(), $linkedTitles ) ) {
				continue;
			}

			$userCan = $this->permissionManager->userCan( 'read', $this->context->getUser(), $title );
			if ( !$userCan ) {
				continue;
			}

			$target = $title->getPrefixedDBKey();

			$pageData = [
				'type' => 'page',
				'target' => $target
			];

			$linkedTitles[$target] = $pageData;
		}

		ksort( $linkedTitles );

		return $linkedTitles;
	}

	/**
	 * @param string $url
	 * @return Title|null
	 */
	private function getTitleFromUrl( string $url ): ?Title {
		$urlHelper = new UrlHelper( $this->config, $this->titleFactory );
		$title = $urlHelper->getTitleFromUrl( $url );

		return $title;
	}
}
