<?php

namespace MediaWiki\Extension\PDFCreator\Rest;

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\PDFCreator\Factory\ExportSpecificationFactory;
use MediaWiki\Extension\PDFCreator\Factory\ModeFactory;
use MediaWiki\Extension\PDFCreator\Factory\TemplateProviderFactory;
use MediaWiki\Extension\PDFCreator\ITargetResult;
use MediaWiki\Extension\PDFCreator\PDFCreator;
use MediaWiki\Extension\PDFCreator\Utility\BoolValueGet;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use Wikimedia\ParamValidator\ParamValidator;

class Export extends SimpleHandler {

	/** @var TitleFactory */
	private $titleFactory;

	/** @var ExportSpecificationFactory */
	private $exportSpecFactory;

	/** @var TemplateProviderFactory */
	private $templateProviderFactory;

	/** @var ModeFactory */
	private $modeFactory;

	/** @var PDFCreator */
	private $pdfCreator;

	/** @var PermissionManager */
	private $permissionManager;

	/** @var Title */
	private $exportTitle;

	/**
	 * @param TitleFactory $titleFactory
	 * @param ExportSpecificationFactory $exportSpecFactory
	 * @param ModeFactory $modeFactory
	 * @param PDFCreator $pdfCreator
	 * @param PermissionManager $permissionManager
	 */
	public function __construct( TitleFactory $titleFactory,
		ExportSpecificationFactory $exportSpecFactory,
		ModeFactory $modeFactory, PDFCreator $pdfCreator,
		PermissionManager $permissionManager,
		TemplateProviderFactory $templateProviderFactory
	) {
		$this->titleFactory = $titleFactory;
		$this->exportSpecFactory = $exportSpecFactory;
		$this->modeFactory = $modeFactory;
		$this->pdfCreator = $pdfCreator;
		$this->permissionManager = $permissionManager;
		$this->templateProviderFactory = $templateProviderFactory;
		$this->exportTitle = null;
	}

	/**
	 * @inheritDoc
	 */
	public function run() {
		$validated = $this->getValidatedParams();

		$pageId = $validated['pageid'];

		$user = RequestContext::getMain()->getUser();
		$this->exportTitle = $this->titleFactory->newFromID( $pageId );

		if ( !$this->exportTitle ) {
			return $this->getResponseFactory()->createHttpError( 404, [ 'No valid title to export' ] );
		}
		if ( !$this->permissionManager->userCan( 'read', $user, $this->exportTitle ) ) {
			return $this->getResponseFactory()->createHttpError( 404, [ 'No permission to export' ] );
		}

		$data = json_decode( $validated['data'], true );
		if ( !$data ) {
			return $this->getResponseFactory()->createHttpError( 404, [ 'Data not found' ] );
		}
		$relevantTitle = $this->exportTitle;
		if ( isset( $data['relevantTitle'] ) ) {
			$relevantTitle = $this->titleFactory->newFromText( $data['relevantTitle'] );
		}

		if ( !$relevantTitle ) {
			return $this->getResponseFactory()->createHttpError( 404, [ 'Relevanttitle not successful' ] );
		}

		$context = new ExportContext(
			$user,
			$relevantTitle
		);

		$mode = isset( $data['mode'] ) ? $data['mode'] : 'page';

		$template = null;
		$options = [];
		if ( isset( $data['template'] ) && $data['template'] !== '' ) {
			$templateProvider = $this->templateProviderFactory->getTemplateProviderFor( $data['template'] );
			$template = $templateProvider->getTemplate( $context, $data['template'] );
			if ( !$template ) {
				return $this->getResponseFactory()->createHttpError( 404, [ 'Invalid template requested' ] );
			}
			$options = $template->getOptions();
		} else {
			$allTemplateNames = $this->templateProviderFactory->getAvailableTemplateNames();
			if ( empty( $allTemplateNames ) ) {
				return $this->getResponseFactory()->createHttpError( 404, [ 'No templates available' ] );
			}
			$templateProvider = $this->templateProviderFactory->getTemplateProviderFor( $allTemplateNames[0] );
			if ( !$template ) {
				return $this->getResponseFactory()->createHttpError( 404, [ 'Invalid template requested' ] );
			}
			$options = $template->getOptions();
		}

		$title = $relevantTitle->getText();
		if ( isset( $options['nsPrefix'] ) && BoolValueGet::from( $options['nsPrefix'] ) === true ) {
			$title = $relevantTitle->getPrefixedText();
		}

		$params = [
			'mode' => $mode,
			'template' => $template ? $data['template'] : null,
			'title' => $title,
			'filename' => $relevantTitle->getPrefixedDBkey() . '.pdf'
		];

		$modeProvider = $this->modeFactory->getModeProvider( $mode );
		$pages = $modeProvider->getExportPages( $this->exportTitle, $data );
		if ( $mode === 'page' || $mode === 'pageWithLinkedPages' || $mode === 'pageWithSubpages' ) {
			$mode = 'batch';
		}
		$specParams = [
			'params' => $params,
			'pages' => $pages,
			'module' => $mode,
			'target' => 'download'
		];

		if ( isset( $data['redirect'] ) ) {
			$noRedirect = false;
			if ( $data['redirect'] === 'no' ) {
				$noRedirect = true;
			}
			$specParams['options'] = [
				'no-redirect' => $noRedirect
			];
		}

		$spec = $this->exportSpecFactory->createNewSpec( $specParams );

		$result = $this->pdfCreator->create( $spec, $context );
		$exportResult = $result->getResult();
		if ( $exportResult instanceof ITargetResult === false ) {
			$response = $this->getResponseFactory()->createHttpError( 404,
				[ 'PDFCreator return value does not contain ITargetResult' ] );
			$response->setHeader( 'X-Error-Details', 'PDFCreator return value does not contain ITargetResult' );
			return $response;
		}
		$exportStatus = $exportResult->getStatus();
		if ( !$exportStatus ) {
			$response = $this->getResponseFactory()->createHttpError( 404, [ $exportStatus->getText() ] );
			$response->setHeader( 'X-Error-Details', $exportStatus->getText() );
			return $response;
		}
		$filename = $exportResult->getFilename();
		$exportData = $exportResult->getData();

		$pdfData = '';
		if ( isset( $exportData['data'] ) ) {
			$pdfData = $exportData['data'];
		}
		if ( empty( $pdfData ) ) {
			$response = $this->getResponseFactory()->createHttpError( 404, [ 'Empty pdf content' ] );
			$response->setHeader( 'X-Error-Details', 'Empty pdf content' );
			return $response;
		}
		$encodedFilename = rawurlencode( $filename );
		$response = $this->getResponseFactory()->create();
		$response->setHeader( 'Content-Type', 'application/pdf' );
		$response->setHeader( 'Content-Disposition', 'attachment; filename="' . $encodedFilename . '"' );
		$response->setHeader( 'Content-Length', strlen( $pdfData ) );
		$response->setHeader( 'X-Filename', $encodedFilename );
		$response->getBody()->write( $pdfData );

		$response->getBody()->rewind();

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function getParamSettings() {
		return [
			'pageid' => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
			'data' => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false
			]
		];
	}

}
