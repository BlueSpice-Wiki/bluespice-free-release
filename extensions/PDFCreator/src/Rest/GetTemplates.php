<?php

namespace MediaWiki\Extension\PDFCreator\Rest;

use MediaWiki\Extension\PDFCreator\PDFCreatorUtil;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Title\TitleFactory;

class GetTemplates extends SimpleHandler {

	/** @var PDFCreatorUtil */
	private $pdfCreatorUtil;

	/** @var TitleFactory */
	private $titleFactory;

	/**
	 * @param PDFCreatorUtil $pdfCreatorUtil
	 * @param TitleFactory $titleFactory
	 */
	public function __construct( PDFCreatorUtil $pdfCreatorUtil, TitleFactory $titleFactory ) {
		$this->pdfCreatorUtil = $pdfCreatorUtil;
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function run() {
		$templates = [];
		$allWikiTemplates = $this->pdfCreatorUtil->getAllProviderTemplateNames();

		foreach ( $allWikiTemplates as $provider => $providerTemplates ) {
			if ( $provider === 'wiki' ) {
				foreach ( $providerTemplates as $template ) {
					$templateTitle = $this->titleFactory->newFromText( 'PDFCreator/' . $template, NS_MEDIAWIKI );
					$templates[] = [
						'template' => $template,
						'url' => $templateTitle->getLocalURL()
					];
				}
				continue;
			}
			foreach ( $providerTemplates as $template ) {
				$templates[] = [
					'template' => $template,
					'disabled' => true
				];
			}
		}
		return $this->getResponseFactory()->createJson( $templates );
	}

}
