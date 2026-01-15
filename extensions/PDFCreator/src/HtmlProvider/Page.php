<?php

namespace MediaWiki\Extension\PDFCreator\HtmlProvider;

use DOMDocument;
use MediaWiki\Extension\PDFCreator\Factory\PageParamsFactory;
use MediaWiki\Extension\PDFCreator\PDFCreator;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Extension\PDFCreator\Utility\MustacheTemplateParser;
use MediaWiki\Extension\PDFCreator\Utility\PageContext;
use MediaWiki\Extension\PDFCreator\Utility\PageSpec;
use MediaWiki\Extension\PDFCreator\Utility\Template;
use MediaWiki\Extension\PDFCreator\Utility\WikiTemplateParser;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\RevisionRenderer;
use MediaWiki\Title\TitleFactory;

class Page extends Raw {

	/** @var RevisionRenderer */
	private $revisionRenderer;

	/** @var RevisionLookup */
	private $revisionLookup;

	/** @var HookContainer */
	private $hookContainer;

	/**
	 * @param TitleFactory $titleFactory
	 * @param PageParamsFactory $pageParamsFactory
	 * @param WikiTemplateParser $wikiTemplateParser
	 * @param MustacheTemplateParser $mustacheTemplateParser
	 * @param RevisionRenderer $revisionRenderer
	 * @param RevisionLookup $revisionLookup
	 * @param HookContainer $hookContainer
	 */
	public function __construct(
		TitleFactory $titleFactory, PageParamsFactory $pageParamsFactory,
		WikiTemplateParser $wikiTemplateParser, MustacheTemplateParser $mustacheTemplateParser,
		RevisionRenderer $revisionRenderer, RevisionLookup $revisionLookup, HookContainer $hookContainer
	) {
		parent::__construct(
			$titleFactory, $pageParamsFactory, $wikiTemplateParser, $mustacheTemplateParser
		);
		$this->revisionRenderer = $revisionRenderer;
		$this->revisionLookup = $revisionLookup;
		$this->hookContainer = $hookContainer;
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'page';
	}

	/**
	 * @param PageSpec $pageSpec
	 * @param Template $template
	 * @param ExportContext $context
	 * @param string $workspace
	 * @return DOMDocument
	 */
	public function getDOMDocument(
		PageSpec $pageSpec, Template $template, ExportContext $context, string $workspace
	): DOMDocument {
		$dom = new DOMDocument();
		$dom->loadXML( PDFCreator::HTML_STUB );

		$title = $this->titleFactory->newFromText( $pageSpec->getPrefixedDBKey() );

		if ( !$pageSpec->getRevisionId() ) {
			$revisionId = $title->getLatestRevID();
		} else {
			$revisionId = $pageSpec->getRevisionId();
		}

		$revisionRecord = $this->revisionLookup->getRevisionByTitle( $title, $revisionId );
		if ( !$revisionRecord ) {
			// Fallback is spec contains wrong revision id
			$revisionId = $title->getLatestRevID();
			$revisionRecord = $this->revisionLookup->getRevisionByTitle( $title, $revisionId );
		}
		if ( $revisionRecord ) {
			$this->hookContainer->run(
				'PDFCreatorAfterSetRevision',
				[ &$revisionRecord, $context->getUserIdentity(), $pageSpec->getParams() ]
			);
		}

		$pageParams = array_merge(
			$this->pageParamsFactory->getParams( $context->getPageIdentity(), $context->getUserIdentity() ),
			$this->pageParamsFactory->getParams( $title->toPageIdentity(), $context->getUserIdentity() ),
			$template->getParams()
		);
		$pageParams['title'] = htmlspecialchars( $pageSpec->getLabel() );

		$classes = [
			'pdfcreator-page',
			'pdfcreator-type-' . $this->getKey(),
			'ns-' . $title->getNamespace(),
			'page-' . str_replace( ':', '_', $title->getPrefixedDBkey() )
		];
		if ( !$revisionRecord ) {
			$classes[] = 'pdfcreator-page-new';
		}

		// Export context holds relevant page as title. This is not necessarily the same as page title.
		$data = $pageSpec->getParams();
		$data['revId'] = $revisionRecord ? $revisionRecord->getId() : 0;
		$pageContext = new PageContext(
			$title,
			$context->getUserIdentity(),
			$data
		);

		$body = $dom->getElementsByTagName( 'body' )->item( 0 );
		$wrapper = $dom->createElement( 'div', '' );
		$wrapper->setAttribute( 'class', implode( ' ', $classes ) );
		$wrapper->setAttribute( 'data-revId', $revisionRecord ? (string)$revisionRecord->getId() : '' );

		$this->addRunningElement( PDFCreator::HEADER, $title->toPageIdentity(),
			$workspace, $template, $wrapper, $pageParams );
		$this->addRunningElement( PDFCreator::FOOTER, $title->toPageIdentity(),
			$workspace, $template, $wrapper, $pageParams );

		$pageParams['content'] = $this->getPageTitle( $pageSpec, $pageParams['title'] );
		if ( !$revisionRecord ) {
			$pageParams['content'] .= '<p>' . wfMessage( 'pdfcreator-content-non-existing-page' ) . '</p>';
		} else {
			$pageParams['content'] .= $this->getPageContent( $revisionRecord, $pageContext );
		}
		$this->addPageContent( $pageSpec, $title, $workspace, $template, $wrapper, $pageParams );

		$body->appendChild( $wrapper );

		$this->hookContainer->run(
			'PDFCreatorAfterGetDOMDocument',
			[ $dom, $pageContext ]
		);

		return $dom;
	}

	/**
	 * @param RevisionRecord $revisionRecord
	 * @param PageContext $context
	 * @return string
	 */
	private function getPageContent( RevisionRecord $revisionRecord, PageContext $context ): string {
		$user = $context->getUser();
		$this->hookContainer->run( 'PDFCreatorContextBeforeGetPage', [ $context ] );
		$options = ParserOptions::newFromContext( $context );
		$options->setSuppressSectionEditLinks();

		$renderedRevision = $this->revisionRenderer->getRenderedRevision( $revisionRecord, $options, $user );
		$output = $renderedRevision->getRevisionParserOutput();

		$html = new DOMDocument();
		// ParserOutput->getText() is deprecated and the replacement is
		// runOutputPipeline which returns a ParserOutput as well
		// so its required to get text with getContentHolderText which
		// is currently marked as unstable but on many different places its used
		// as well like EditPage in mediawiki core but also in other extensions -
		// thats why we decided to use it as well for now
		$text = $output->runOutputPipeline( $options )->getContentHolderText();
		$htmlText = "<!DOCTYPE html><html><head><meta charset=\"UTF-8\"></head><body>" . $text . "</body></html>";
		$html->loadHTML( $htmlText );
		$html->documentElement->setAttribute( 'xmlns', 'http://www.w3.org/1999/xhtml' );
		$node = $html->getElementsByTagName( 'body' )->item( 0 )->firstChild;
		$xHtml = $html->saveXML( $node );
		return $xHtml;
	}

}
