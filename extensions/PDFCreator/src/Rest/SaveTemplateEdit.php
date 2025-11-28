<?php

namespace MediaWiki\Extension\PDFCreator\Rest;

use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Content\CssContent;
use MediaWiki\Content\JsonContent;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\PDFCreator\MediaWiki\Content\PDFCreatorTemplate;
use MediaWiki\Extension\PDFCreator\PDFCreatorUtil;
use MediaWiki\Language\FormatterFactory;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Revision\RevisionRecord;
use Wikimedia\ParamValidator\ParamValidator;

class SaveTemplateEdit extends SimpleHandler {

	/** @var PDFCreatorUtil */
	private $util;

	/** @var WikiPageFactory */
	private $wikiPageFactory;

	/** @var FormatterFactory */
	private $formatterFactory;

	/**
	 *
	 * @param PDFCreatorUtil $util
	 * @param WikiPageFactory $wikiPageFactory
	 * @param FormatterFactory $formatterFactory
	 */
	public function __construct(
		PDFCreatorUtil $util,
		WikiPageFactory $wikiPageFactory,
		FormatterFactory $formatterFactory ) {
		$this->util = $util;
		$this->wikiPageFactory = $wikiPageFactory;
		$this->formatterFactory = $formatterFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function run() {
		$validated = $this->getValidatedParams();
		$body = $this->getValidatedBody();
		$pageTitle = $validated['pagetitle'];

		$title = $this->util->createPDFTemplateTitle( $pageTitle );
		$wikiPage = $this->wikiPageFactory->newFromTitle( $title );
		$context = RequestContext::getMain();
		$user = $context->getUser();
		$updater = $wikiPage->newPageUpdater( $user );
		$data = $body['data'];

		if ( !$data ) {
			return $this->getResponseFactory()->createHttpError( 404, [ 'Data not found' ] );
		}
		foreach ( $data as $slotKey => $content ) {
			if ( $slotKey === 'pdfcreator_template_styles' ) {
				$content = new CssContent( $content );
			} elseif ( $slotKey === 'pdfcreator_template_options' ) {
				$content = new JsonContent( $content );
			} else {
				$content = new PDFCreatorTemplate( $content );
			}
			$updater->setContent( $slotKey, $content );
		}

		$rev = $updater->saveRevision(
			CommentStoreComment::newUnsavedComment( 'Update pdf template' ) );
		if ( !$rev instanceof RevisionRecord ) {
			$status = $updater->getStatus();
			$statusFormatter = $this->formatterFactory->getStatusFormatter( $context );
			return $this->getResponseFactory()->createHttpError( 404,
				[ $statusFormatter->getMessage( $updater->getStatus() )->text() ] );
		}

		$status = $updater->wasRevisionCreated();
		return $this->getResponseFactory()->createJson(
			[ 'status' => $status, 'title' => $title->getPrefixedText() ]
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getParamSettings() {
		return [
			'pagetitle' => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			]
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getBodyParamSettings(): array {
		return [
			'data' => [
				self::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'array',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_DEFAULT => ''
			]
		];
	}
}
