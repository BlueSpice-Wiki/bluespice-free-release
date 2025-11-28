<?php

namespace MediaWiki\Extension\PDFCreator\Factory;

use DOMDocument;
use MediaWiki\Extension\PDFCreator\IHtmlProvider;
use MediaWiki\Extension\PDFCreator\PDFCreator;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Extension\PDFCreator\Utility\ExportPage;
use MediaWiki\Extension\PDFCreator\Utility\PageSpec;
use MediaWiki\Extension\PDFCreator\Utility\Template;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class ExportPageFactory implements LoggerAwareInterface {

	/** @var LoggerInterface */
	private $logger;

	/** @var HtmlProviderFactory */
	private $htmlProviderFactory;

	/** @var PageParamsFactory */
	private $pageParamsFactory;

	/**
	 * @param HtmlProviderFactory $htmlProviderFactory
	 * @param PageParamsFactory $pageParamsFactory
	 */
	public function __construct(
		HtmlProviderFactory $htmlProviderFactory, PageParamsFactory $pageParamsFactory ) {
		$this->htmlProviderFactory = $htmlProviderFactory;
		$this->pageParamsFactory = $pageParamsFactory;
	}

	/**
	 * @param LoggerInterface $logger
	 * @return void
	 */
	public function setLogger( LoggerInterface $logger ): void {
		$this->logger = $logger;
	}

	/**
	 *
	 * @param PageSpec $pageSpec
	 * @param Template $template
	 * @param ExportContext $context
	 * @param string $workspace
	 * @return ExportPage
	 */
	public function getPageFromSpec( PageSpec $pageSpec, Template $template,
		ExportContext $context, string $workspace ): ExportPage {
		$provider = $this->htmlProviderFactory->getProvider( $pageSpec->getType() );
		if ( !$provider instanceof IHtmlProvider ) {
			$this->logger->error( "PDFCreator module batch not valid html provider for {$pageSpec->getType()}" );
			$dom = new DOMDocument();
			$dom->loadXML( PDFCreator::HTML_STUB );
		} else {
			$dom = $provider->getDOMDocument( $pageSpec, $template, $context, $workspace );
		}

		$label = $pageSpec->getLabel();
		$heading = $dom->getElementById( $pageSpec->getUniqueId() );

		$headings = $dom->getElementsByTagName( 'h1' );
		foreach ( $headings as $heading ) {
			$class = $heading->getAttribute( 'class' );
			if ( str_contains( $class, 'firstHeading' ) ) {
				$label = $heading->nodeValue;
			}
		}

		return new ExportPage(
			$pageSpec->getType(), $dom, $label,
			$pageSpec->getPrefixedDBKey(), $pageSpec->getParams(), $pageSpec->getUniqueId()
		);
	}
}
