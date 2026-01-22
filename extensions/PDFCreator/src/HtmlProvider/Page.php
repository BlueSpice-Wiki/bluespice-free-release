<?php

namespace MediaWiki\Extension\PDFCreator\HtmlProvider;

use DOMDocument;
use DOMElement;
use DOMException;
use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\PDFCreator\Factory\PageParamsFactory;
use MediaWiki\Extension\PDFCreator\PDFCreator;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Extension\PDFCreator\Utility\MustacheTemplateParser;
use MediaWiki\Extension\PDFCreator\Utility\PageContext;
use MediaWiki\Extension\PDFCreator\Utility\PageSpec;
use MediaWiki\Extension\PDFCreator\Utility\Template;
use MediaWiki\Extension\PDFCreator\Utility\WikiTemplateParser;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Html\Html;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\RevisionRenderer;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\User;

class Page extends Raw {

	/** @var RevisionRenderer */
	protected $revisionRenderer;

	/** @var RevisionLookup */
	protected $revisionLookup;

	/** @var HookContainer */
	protected $hookContainer;

	/** @var int|null */
	protected $revisionId = null;

	/** @var array<string, mixed> */
	private array $originalRequestValues = [];

	/**
	 * @param TitleFactory $titleFactory
	 * @param PageParamsFactory $pageParamsFactory
	 * @param WikiTemplateParser $wikiTemplateParser
	 * @param MustacheTemplateParser $mustacheTemplateParser
	 * @param RevisionRenderer $revisionRenderer
	 * @param RevisionLookup $revisionLookup
	 * @param HookContainer $hookContainer
	 * @param IContextSource|null $requestContext
	 */
	public function __construct(
		TitleFactory $titleFactory, PageParamsFactory $pageParamsFactory,
		WikiTemplateParser $wikiTemplateParser, MustacheTemplateParser $mustacheTemplateParser,
		RevisionRenderer $revisionRenderer,
		RevisionLookup $revisionLookup,
		HookContainer $hookContainer,
		private ?IContextSource $requestContext = null
	) {
		parent::__construct(
			$titleFactory, $pageParamsFactory, $wikiTemplateParser, $mustacheTemplateParser
		);
		$this->revisionRenderer = $revisionRenderer;
		$this->revisionLookup = $revisionLookup;
		$this->hookContainer = $hookContainer;

		if ( $this->requestContext === null ) {
			$this->requestContext = RequestContext::getMain();
		}
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
	 *
	 * @return DOMDocument
	 * @throws DOMException
	 */
	public function getDOMDocument(
		PageSpec $pageSpec, Template $template, ExportContext $context, string $workspace
	): DOMDocument {
		$dom = new DOMDocument();
		$dom->loadXML( PDFCreator::HTML_STUB );

		$title = $this->titleFactory->newFromText( $pageSpec->getPrefixedDBKey() );
		$revisionRecord = $this->getRevisionRecord( $pageSpec, $title, $context );
		if ( $revisionRecord ) {
			$this->revisionId = $revisionRecord->getId();
		}

		// Export context holds relevant page as title. This is not necessarily the same as page title.
		$data = $pageSpec->getParams();
		$data['revId'] = $revisionRecord ? $revisionRecord->getId() : 0;
		$pageContext = new PageContext(
			$title,
			User::newFromIdentity( $context->getUserIdentity() ),
			$data
		);

		$classes = [
			'pdfcreator-page',
			'pdfcreator-type-' . $this->getKey(),
			'ns-' . $title->getNamespace(),
			'page-' . str_replace( ':', '_', $title->getPrefixedDBkey() )
		];

		$parserOutput = null;
		if ( !$revisionRecord ) {
			$classes[] = 'pdfcreator-page-new';
		} else {
			$this->requestContext->setUser( $pageContext->getUser() );
			$this->requestContext->setTitle( $pageContext->getTitle() );

			// Modify the request to get correct parser output
			$this->modifyRequest( $data );

			$this->hookContainer->run(
				'PDFCreatorAfterSetRevision',
				[ &$revisionRecord, $context->getUserIdentity(), $pageSpec->getParams() ]
			);

			$parserOptions = ParserOptions::newFromContext( $this->requestContext );
			$parserOptions->setSuppressSectionEditLinks();
			$parserOutput = $this->getParserOutput( $revisionRecord, $pageContext, $parserOptions );
		}

		$pageParams = array_merge(
			$this->pageParamsFactory->getParams( $context->getPageIdentity(), $context->getUserIdentity() ),
			$this->pageParamsFactory->getParams( $title->toPageIdentity(), $context->getUserIdentity() ),
			$template->getParams()
		);
		$pageParams['title'] = $this->getPageParamsTitle(
			$title, $pageSpec, $parserOutput, $template, $data
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
		if ( !$parserOutput ) {
			$pageParams['content'] .= '<p>' . wfMessage( 'pdfcreator-content-non-existing-page' ) . '</p>';
		} else {
			$pageParams['content'] .= $this->getEmptyPageBugFix();
			$pageParams['content'] .= $this->getPageContent( $parserOutput, $parserOptions );
			$pageParams['content'] .= $this->getEmptyPageBugFix();
		}

		$this->restoreOriginalRequest();

		$this->addPageContent( $pageSpec, $title, $workspace, $template, $wrapper, $pageParams );

		$body->appendChild( $wrapper );

		$this->hookContainer->run(
			'PDFCreatorAfterGetDOMDocument',
			[ $dom, $pageContext ]
		);

		return $dom;
	}

	/**
	 * @param Title $title
	 * @param PageSpec $pageSpec
	 * @param ParserOutput|null $parserOutput
	 * @param Template $template
	 * @param array $data
	 * @return string
	 */
	protected function getPageParamsTitle(
		Title $title, PageSpec $pageSpec, ?ParserOutput $parserOutput, Template $template, array $data ): string {
		$pageParamsTitle = $pageSpec->getLabel();
		if ( !isset( $data['force-label'] ) ) {
			if ( $parserOutput ) {
				$parserLabel = $this->getParserPageTitle( $parserOutput, $data );
				$pageParamsTitle = $parserLabel;
			}

			if ( !isset( $data['display-title'] ) ) {
				$templateOptions = $template->getOptions();
				if ( isset( $templateOptions['nsPrefix'] ) && $templateOptions['nsPrefix'] === true ) {
					if ( !str_contains( $pageParamsTitle, $title->getPrefixedText() ) ) {
						$pageParamsTitle = str_replace(
							$title->getText(), $title->getPrefixedText(), $pageParamsTitle
						);
					}
				} elseif ( !isset( $templateOptions['nsPrefix'] ) || $templateOptions['nsPrefix'] === false ) {
					$pageParamsTitle = str_replace(
						$title->getPrefixedText(), $title->getText(), $pageParamsTitle
					);
				}
			}
		}
		return $pageParamsTitle;
	}

	/**
	 * @param PageSpec $pageSpec
	 * @param Title $title
	 * @param ExportContext $context
	 * @return RevisionRecord|null
	 */
	protected function getRevisionRecord( PageSpec $pageSpec, Title $title, ExportContext $context ): ?RevisionRecord {
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

		return $revisionRecord;
	}

	/**
	 * @param RevisionRecord $revisionRecord
	 * @param PageContext $context
	 * @return ParserOutput
	 */
	protected function getParserOutput(
		RevisionRecord $revisionRecord, PageContext $context, ParserOptions $parserOptions
	): ParserOutput {
		$this->hookContainer->run( 'PDFCreatorContextBeforeGetPage', [ $context ] );

		$renderedRevision = $this->revisionRenderer->getRenderedRevision(
			$revisionRecord, $parserOptions, $context->getUser()
		);

		$output = $renderedRevision->getRevisionParserOutput();

		return $output;
	}

	/**
	 * @param ParserOutput $parserOutput
	 * @return string
	 */
	private function getParserPageTitle( ParserOutput $parserOutput ): string {
		$parserTitle = $parserOutput->getTitleText();
		$parserTitle = strip_tags( $parserTitle );

		return $parserTitle;
	}

	/**
	 * @param ParserOutput $parserOutput
	 * @param ParserOptions $parserOptions
	 * @return string
	 */
	private function getPageContent( ParserOutput $parserOutput, ParserOptions $parserOptions ): string {
		$html = new DOMDocument();
		// ParserOutput->getText() is deprecated and the replacement is
		// runOutputPipeline which returns a ParserOutput as well
		// so its required to get text with getContentHolderText which
		// is currently marked as unstable but on many different places its used
		// as well like EditPage in mediawiki core but also in other extensions -
		// thats why we decided to use it as well for now
		$text = $parserOutput->runOutputPipeline( $parserOptions )->getContentHolderText();
		$htmlText = "<!DOCTYPE html><html><head><meta charset=\"UTF-8\"></head><body>" . $text . "</body></html>";
		$html->loadHTML( $htmlText );
		$html->documentElement->setAttribute( 'xmlns', 'http://www.w3.org/1999/xhtml' );
		$node = $html->getElementsByTagName( 'body' )->item( 0 )->firstChild;
		$xHtml = $html->saveXML( $node );
		return $xHtml;
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

		if ( $this->revisionId !== null ) {
			$this->wikiTemplateParser->setRevisionId( $this->revisionId );
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
	 * For the processing pipeline, in order to get correct parser output we might need to modify the request in
	 * the way it would be done on a normal page view request.
	 * E.g. for ContentStabilization
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	private function modifyRequest( array $data ): void {
		$request = $this->requestContext->getRequest();
		$this->originalRequestValues = $request->getValues();

		foreach ( $data as $key => $value ) {
			if ( !is_scalar( $value ) ) {
				continue;
			}

			if ( $key === 'revId' ) {
				$key = 'oldid';
			}

			$request->setVal( $key, $value );
		}
	}

	/**
	 * Restore original request values after modification.
	 *
	 * @return void
	 */
	private function restoreOriginalRequest(): void {
		if ( empty( $this->originalRequestValues ) ) {
			return;
		}

		$request = $this->requestContext->getRequest();

		foreach ( $request->getValues() as $key => $value ) {
			$originalValue = $this->originalRequestValues[$key] ?? null;

			if ( $originalValue === null ) {
				$request->unsetVal( $key );
				continue;
			}

			$request->setVal( $key, $originalValue );
		}
	}

	/**
	 * I am here to prevent empty page bug
	 *
	 * @return string
	 */
	private function getEmptyPageBugFix(): string {
		return Html::element(
			'span',
			[
				'style' => 'visibility:hidden; max-height: 0;'
			],
			'&nbsp;'
		);
	}
}
