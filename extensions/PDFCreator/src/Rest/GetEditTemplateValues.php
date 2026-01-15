<?php

namespace MediaWiki\Extension\PDFCreator\Rest;

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\PDFCreator\Factory\PageParamsFactory;
use MediaWiki\Extension\PDFCreator\Utility\TemplateValueExtractor;
use MediaWiki\Rest\SimpleHandler;
use Wikimedia\ParamValidator\ParamValidator;

class GetEditTemplateValues extends SimpleHandler {

	/** @var TemplateValueExtractor */
	private $extractor;

	/** @var PageParamsFactory */
	private $pageParamsFactory;

	/**
	 * @param TemplateValueExtractor $extractor
	 * @param PageParamsFactory $pageparamsFactory
	 */
	public function __construct( TemplateValueExtractor $extractor, PageParamsFactory $pageparamsFactory ) {
		$this->extractor = $extractor;
		$this->pageParamsFactory = $pageparamsFactory;
	}

	public function run() {
		$validated = $this->getValidatedParams();
		$templateName = $validated['templatename'];
		$context = RequestContext::getMain();
		$languageCode = $context->getLanguage()->getCode();

		$values = $this->extractor->getTemplateValues( $templateName );
		if ( isset( $values['errors'] ) ) {
			$parsedErrors = [];
			foreach ( $values['errors'] as $key => $error ) {
				$parsedErrors[ $key ] = $context->msg( $error )->text();
			}
			$values['errors'] = $parsedErrors;
		}
		$params = $this->getTemplateParams( $languageCode );
		return $this->getResponseFactory()->createJson( [ 'values' => $values, 'params' => $params ] );
	}

	/**
	 * @param string $languageCode
	 * @return array
	 */
	private function getTemplateParams( $languageCode ) {
		$params = [];

		$pageParamsDesc = $this->pageParamsFactory->getParamDescription( $languageCode );
		foreach ( $pageParamsDesc as $key => $desc ) {
			$param[ 'key' ] = $desc->getKey();
			$param[ 'example' ] = $desc->getExampleValue();
			$params[ $desc->getKey() ] = $param;
		}
		return $params;
	}

	/**
	 * @inheritDoc
	 */
	public function getParamSettings() {
		return [
			'templatename' => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_DEFAULT => ''
			]
		];
	}

}
