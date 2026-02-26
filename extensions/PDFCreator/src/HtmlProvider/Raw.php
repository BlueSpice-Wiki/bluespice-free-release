<?php

namespace MediaWiki\Extension\PDFCreator\HtmlProvider;

use DOMDocument;
use DOMElement;
use MediaWiki\Extension\PDFCreator\Factory\PageParamsFactory;
use MediaWiki\Extension\PDFCreator\IHtmlProvider;
use MediaWiki\Extension\PDFCreator\PDFCreator;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Extension\PDFCreator\Utility\MustacheTemplateParser;
use MediaWiki\Extension\PDFCreator\Utility\PageSpec;
use MediaWiki\Extension\PDFCreator\Utility\Template;
use MediaWiki\Extension\PDFCreator\Utility\WikiTemplateParser;
use MediaWiki\Html\Html;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;

class Raw implements IHtmlProvider {

	/** @var TitleFactory */
	protected $titleFactory;

	/** @var PageParamsFactory */
	protected $pageParamsFactory;

	/** @var WikiTemplateParser */
	protected $wikiTemplateParser;

	/** @var MustacheTemplateParser */
	protected $mustacheTemplateParser;

	/**
	 * @param TitleFactory $titleFactory
	 * @param PageParamsFactory $pageParamsFactory
	 * @param WikiTemplateParser $wikiTemplateParser
	 * @param MustacheTemplateParser $mustacheTemplateParser
	 */
	public function __construct(
		TitleFactory $titleFactory, PageParamsFactory $pageParamsFactory,
		WikiTemplateParser $wikiTemplateParser, MustacheTemplateParser $mustacheTemplateParser
	) {
		$this->titleFactory = $titleFactory;
		$this->pageParamsFactory = $pageParamsFactory;
		$this->wikiTemplateParser = $wikiTemplateParser;
		$this->mustacheTemplateParser = $mustacheTemplateParser;
	}

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'raw';
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
		$title = $this->titleFactory->newFromPageIdentity( $context->getPageIdentity() );

		$pageParams = array_merge(
			$this->pageParamsFactory->getParams( $context->getPageIdentity(), $context->getUserIdentity() ),
			$template->getParams()
		);
		$pageParams['title'] = htmlspecialchars( $pageSpec->getLabel() );

		$classes = [
			'pdfcreator-page',
			'pdfcreator-type-' . $this->getKey()
		];

		$dom = new DOMDocument();
		$dom->loadXML( PDFCreator::HTML_STUB );
		$body = $dom->getElementsByTagName( 'body' )->item( 0 );
		$wrapper = $dom->createElement( 'div', '' );
		$wrapper->setAttribute( 'class', implode( ' ', $classes ) );

		$this->addRunningElement( PDFCreator::HEADER, $title->toPageIdentity(),
			$workspace, $template, $wrapper, $pageParams );
		$this->addRunningElement( PDFCreator::FOOTER, $title->toPageIdentity(),
			$workspace, $template, $wrapper, $pageParams );

		$pageParams['content'] = $this->getPageTitle( $pageSpec, $pageParams['title'] );
		$params = $pageSpec->getParams();
		if ( isset( $pageParams['content'] ) ) {
			$pageParams['content'] .= $params['content'];
		}
		$this->addPageContent( $pageSpec, $title, $workspace, $template, $wrapper, $pageParams );

		$body->appendChild( $wrapper );
		return $dom;
	}

	/**
	 * @param PageSpec $pageSpec
	 * @param string $displaytitle
	 * @return string
	 */
	protected function getPageTitle( PageSpec $pageSpec, string $displaytitle ): string {
		$html = Html::openElement(
			'h1',
			[
				'class' => 'firstHeading',
				'id' => $pageSpec->getUniqueId()
			]
		);
		$html .= $displaytitle;
		$html .= Html::closeElement( 'h1' );

		return $html;
	}

	/**
	 * @param string $key
	 * @param PageIdentity|null $pageIdentity
	 * @param string $workspace
	 * @param Template $template
	 * @param DOMElement $body
	 * @param array $params
	 * @return void
	 */
	protected function addRunningElement(
		string $key, ?PageIdentity $pageIdentity, string $workspace, Template $template,
		DOMElement $body, $params = []
	): void {
		$path = "{$workspace}/{$key}.mustache";

		if ( $key === PDFCreator::HEADER ) {
			$input = $template->getHeader();
		} elseif ( $key === PDFCreator::FOOTER ) {
			$input = $template->getFooter();
		} else {
			return;
		}

		$parsedWiki = $this->wikiTemplateParser->execute( $input, $pageIdentity );
		if ( $parsedWiki === '' ) {
			return;
		}

		file_put_contents( $path, $parsedWiki );
		$parsedMustache = $this->mustacheTemplateParser->execute( $workspace, $key, $params );
		unlink( $path );

		$templateFragment = $body->ownerDocument->createDocumentFragment();
		$templateFragment->appendXML( $parsedMustache );

		$body->appendChild( $templateFragment );
	}

	/**
	 * @param PageSpec $pageSpec
	 * @param Title $title
	 * @param string $workspace
	 * @param Template $template
	 * @param DOMElement $body
	 * @param array $params
	 * @return void
	 */
	protected function addPageContent(
		PageSpec $pageSpec, Title $title, string $workspace, Template $template,
		DOMElement $body, $params = []
	): void {
		$key = PDFCreator::CONTENT;
		$path = "{$workspace}/{$key}.mustache";
		$input = $template->getBody();

		$parsedWiki = $this->wikiTemplateParser->execute( $input, $title->toPageIdentity() );
		if ( $parsedWiki === '' ) {
			return;
		}
		file_put_contents( $path, $parsedWiki );
		$parsedMustache = $this->mustacheTemplateParser->execute( $workspace, $key, $params );
		unlink( $path );

		$templateFragment = $body->ownerDocument->createDocumentFragment();
		$templateFragment->appendXML( $parsedMustache );

		$contentContainer = $body->ownerDocument->createElement( 'div' );
		$contentContainer->setAttribute( 'class', 'pdfcreator-page-content' );
		$contentContainer->appendChild( $templateFragment );
		$body->appendChild( $contentContainer );
	}
}
