<?php

namespace Mediawiki\Extension\EnhancedStandardUIs\Rest;

use MediaWiki\Context\RequestContext;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Title\TitleFactory;
use RepoGroup;
use Wikimedia\ParamValidator\ParamValidator;

class GetMetadata extends SimpleHandler {

	/** @var TitleFactory */
	private $titleFactory;

	/** @var RepoGroup */
	private $repoGroup;

	/**
	 *
	 * @param TitleFactory $titleFactory
	 * @param RepoGroup $repoGroup
	 */
	public function __construct( TitleFactory $titleFactory, RepoGroup $repoGroup ) {
		$this->titleFactory = $titleFactory;
		$this->repoGroup = $repoGroup;
	}

	/**
	 * @inheritDoc
	 */
	public function run() {
		$validated = $this->getValidatedParams();
		$fileName = $validated[ 'filename' ];
		$fileTitle = $this->titleFactory->newFromText( 'File:' . $fileName );
		if ( !$fileTitle ) {
			return $this->getResponseFactory()->createHttpError( 404, [ 'No valid file title' ] );
		}

		$file = $this->repoGroup->findFile( $fileTitle );
		if ( !$file ) {
			$file = $this->repoGroup->getLocalRepo()->newFile( $fileTitle );
		}
		if ( !$file ) {
			return $this->getResponseFactory()->createHttpError(
				404, [ 'File not found: ' . $fileTitle->getFullText() ]
			);
		}
		$data = [];
		$metaData = $file->formatMetadata( RequestContext::getMain() );
		if ( isset( $metaData['visible'] ) && count( $metaData['visible'] ) > 0 ) {
			$data = array_merge( $metaData['visible'], $data );
		}
		if ( isset( $metaData['collapsed'] ) && count( $metaData['collapsed'] ) > 0 ) {
			$data = array_merge( $metaData['collapsed'], $data );
		}
		return $this->getResponseFactory()->createJson( $data );
	}

	/**
	 * @inheritDoc
	 */
	public function getParamSettings() {
		return [
			'filename' => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			]
		];
	}
}
