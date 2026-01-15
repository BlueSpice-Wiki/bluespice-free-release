<?php

namespace MediaWiki\Extension\PDFCreator\HtmlProvider;

use DOMElement;
use MediaWiki\Extension\PDFCreator\PDFCreator;
use MediaWiki\Extension\PDFCreator\Utility\PageSpec;
use MediaWiki\Extension\PDFCreator\Utility\Template;
use MediaWiki\Title\Title;

class Outro extends Intro {

	/**
	 * @return string
	 */
	public function getKey(): string {
		return 'outro';
	}

	/**
	 * @return string
	 */
	protected function getTemplateSection(): string {
		return PDFCreator::OUTRO;
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
		$input = $template->getOutro();

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
