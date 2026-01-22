<?php

namespace MediaWiki\Extension\PDFCreator\HtmlProvider;

use DOMDocument;
use DOMElement;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\PDFCreator\PDFCreator;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Extension\PDFCreator\Utility\PageContext;
use MediaWiki\Extension\PDFCreator\Utility\PageSpec;
use MediaWiki\Extension\PDFCreator\Utility\Template;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

class Intro extends Page {

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'intro';
	}

	/**
	 * @return string
	 */
	protected function getTemplateSection(): string {
		return PDFCreator::INTRO;
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

		$title = $this->titleFactory->newFromPageIdentity( $context->getPageIdentity() );
		$revisionRecord = $this->getRevisionRecord( $pageSpec, $title, $context );
		if ( $revisionRecord ) {
			$this->revisionId = $revisionRecord->getId();
		}

		$data = $pageSpec->getParams();
		$pageContext = new PageContext(
			$title,
			User::newFromIdentity( $context->getUserIdentity() ),
			$data
		);

		$classes = [
			'pdfcreator-page',
			'pdfcreator-type-' . $this->getKey()
		];

		$parserOutput = null;
		if ( $revisionRecord ) {
			$requestContext = RequestContext::getMain();
			$requestContext->setUser( $pageContext->getUser() );
			$requestContext->setTitle( $pageContext->getTitle() );
			$parserOptions = ParserOptions::newFromContext( $requestContext );
			$parserOptions->setSuppressSectionEditLinks();
			$parserOutput = $this->getParserOutput( $revisionRecord, $pageContext, $parserOptions );
		}

		$pageParams = array_merge(
			$this->pageParamsFactory->getParams( $context->getPageIdentity(), $context->getUserIdentity() ),
			$template->getParams()
		);
		$pageParams['title'] = $this->getPageParamsTitle(
			$title, $pageSpec, $parserOutput, $template, $data
		);

		$body = $dom->getElementsByTagName( 'body' )->item( 0 );
		$wrapper = $dom->createElement( 'div', '' );
		$wrapper->setAttribute( 'class', implode( ' ', $classes ) );

		$this->addPageContent( $pageSpec, $title, $workspace, $template, $wrapper, $pageParams );

		$body->appendChild( $wrapper );
		return $dom;
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
		$key = $this->getTemplateSection();
		$path = "{$workspace}/{$key}.mustache";
		$input = $template->getIntro();

		if ( $this->revisionId !== null ) {
			$this->wikiTemplateParser->setRevisionId( $this->revisionId );
		}
		$parsedWiki = $this->wikiTemplateParser->execute( $input, $title->toPageIdentity() );
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
}
