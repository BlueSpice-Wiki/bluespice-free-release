<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMDocument;
use MediaWiki\Context\IContextSource;
use MediaWiki\Language\Language;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\ParserFactory;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Title\TitleFactory;

class WikiTemplateParser {

	/** @var ParserFactory */
	private $parserFactory;

	/** @var TitleFactory */
	private $titleFactory;

	/** @var Language */
	private $language;

	/** @var IContextSource */
	private $context;

	/**
	 * @param ParserFactory $parserFactory
	 * @param TitleFactory $titleFactory
	 * @param Language $language
	 * @param IContextSource $context
	 */
	public function __construct(
		ParserFactory $parserFactory, TitleFactory $titleFactory,
		Language $language, IContextSource $context
	) {
		$this->parserFactory = $parserFactory;
		$this->titleFactory = $titleFactory;
		$this->language = $language;
		$this->context = $context;
	}

	/**
	 * @param string $input
	 * @param PageIdentity|null $pageIdentity
	 * @return string
	 */
	public function execute( string $input, ?PageIdentity $pageIdentity ): string {
		$title = $this->titleFactory->newFromPageIdentity( $pageIdentity );

		$options = ParserOptions::newFromContext( $this->context );
		$options->setTargetLanguage( $this->language );
		$options->setSuppressSectionEditLinks();

		$parser = $this->parserFactory->getInstance();
		$parser->startExternalParse( $title, $options, Parser::OT_PREPROCESS );
		$output = $parser->preprocess( $input, $title, $options );

		$parsedOutput = $parser->parse( $output, $pageIdentity, $options );

		$html = new DOMDocument();
		// ParserOutput->getText() is deprecated and the replacement is
		// runOutputPipeline which returns a ParserOutput as well
		// so its required to get text with getContentHolderText which
		// is currently marked as unstable but on many different places its used
		// as well like EditPage in mediawiki core but also in other extensions -
		// thats why we decided to use it as well for now
		$text = $parsedOutput->runOutputPipeline( $options )->getContentHolderText();
		$htmlText = "<!DOCTYPE html><html><head><meta charset=\"UTF-8\"></head><body>" . $text . "</body></html>";
		$html->loadHTML( $htmlText );
		$html->documentElement->setAttribute( 'xmlns', 'http://www.w3.org/1999/xhtml' );
		$node = $html->getElementsByTagName( 'body' )->item( 0 )->firstChild;
		$xHtml = $html->saveXML( $node );
		return $xHtml;
	}
}
