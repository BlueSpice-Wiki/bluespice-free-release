<?php

namespace MediaWiki\Extension\PDFCreator\Rest;

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\PDFCreator\Utility\TemplateValueInsertor;
use MediaWiki\Language\FormatterFactory;
use MediaWiki\Rest\SimpleHandler;
use Wikimedia\ParamValidator\ParamValidator;

class SaveEditTemplateValues extends SimpleHandler {

	/** @var TemplateValueInsertor */
	private $insertor;

	/** @var FormatterFactory */
	private $formatterFactory;

	/**
	 * @param TemplateValueInsertor $insertor
	 * @param FormatterFactory $formatterFactory
	 */
	public function __construct( TemplateValueInsertor $insertor, FormatterFactory $formatterFactory ) {
		$this->insertor = $insertor;
		$this->formatterFactory = $formatterFactory;
	}

	public function run() {
		$validated = $this->getValidatedParams();
		$templateName = $validated['templatename'];
		$body = $this->getValidatedBody();
		$data = $body['data'];
		$context = RequestContext::getMain();
		$user = $context->getUser();

		if ( !$data ) {
			return $this->getResponseFactory()->createHttpError( 404, [ 'Data not found' ] );
		}
		$status = $this->insertor->saveTemplateValues( $templateName, $data, $user );

		$statusFormatter = $this->formatterFactory->getStatusFormatter( $context );
		return $this->getResponseFactory()->createJson( [ 'success' => $status->isOK(),
			'status' => $statusFormatter->getWikiText( $status ) ] );
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

	/**
	 * @inheritDoc
	 */
	public function getBodyParamSettings(): array {
		return [
			'data' => [
				ParamValidator::PARAM_TYPE => 'array',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_DEFAULT => ''
			]
		];
	}

}
