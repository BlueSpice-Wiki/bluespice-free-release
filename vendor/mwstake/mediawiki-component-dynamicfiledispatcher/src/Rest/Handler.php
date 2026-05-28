<?php

namespace MWStake\MediaWiki\Component\DynamicFileDispatcher\Rest;

use MediaWiki\Permissions\Authority;
use MediaWiki\Rest\HttpException;
use MediaWiki\Rest\ResponseInterface;
use MediaWiki\Rest\SimpleHandler;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\CacheableFile;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\DynamicFileDispatcherFactory;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\IDynamicFile;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\IDynamicFileModule;
use Wikimedia\ParamValidator\ParamValidator;

class Handler extends SimpleHandler {

	/**
	 * @var DynamicFileDispatcherFactory
	 */
	private $moduleFactory;

	/** @var IDynamicFile|null|false */
	private $resolvedFile = false;

	/**
	 * @param DynamicFileDispatcherFactory $factory
	 */
	public function __construct( DynamicFileDispatcherFactory $factory ) {
		$this->moduleFactory = $factory;
	}

	public function needsReadAccess() {
		return false;
	}

	/**
	 * @return \MediaWiki\Rest\Response
	 */
	public function execute() {
		$file = $this->resolveFile();
		if ( !$file ) {
			throw new HttpException( 'File not found', 404 );
		}

		// Clear output buffer to make sure nothing gets mixed in with file content
		while ( ob_get_level() ) {
			ob_end_clean();
		}

		$response = $this->getResponseFactory()->create();
		$response->setHeader( 'Content-Type', $file->getMimeType() );
		$response->setBody( $file->getStream() );

		return $response;
	}

	/**
	 * Resolve module and file, caching the result for reuse between
	 * getETag() and execute().
	 *
	 * @return IDynamicFile|null
	 */
	private function resolveFile(): ?IDynamicFile {
		if ( $this->resolvedFile !== false ) {
			return $this->resolvedFile;
		}

		$module = $this->getDynamicModule();
		if ( !$module ) {
			$this->resolvedFile = null;
			return null;
		}
		$queryParams = $this->getRequest()->getQueryParams();
		$authority = \RequestContext::getMain()->getUser();
		if ( !( $authority instanceof Authority ) ) {
			$this->resolvedFile = null;
			return null;
		}
		if ( !$module->isAuthorized( $authority, $queryParams ) ) {
			$this->resolvedFile = null;
			return null;
		}
		$this->resolvedFile = $module->getFile( $queryParams );
		return $this->resolvedFile;
	}

	/**
	 * @return IDynamicFileModule|null
	 */
	private function getDynamicModule(): ?IDynamicFileModule {
		$name = $this->getValidatedParams()['module'];
		return $this->moduleFactory->getModule( $name );
	}

	/**
	 * @return array[]
	 */
	public function getParamSettings() {
		return [
			'module' => [
				static::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true
			]
		];
	}

	/**
	 * @inheritDoc
	 */
	public function applyCacheControl( ResponseInterface $response ) {
		$file = $this->resolveFile();
		if ( $file instanceof CacheableFile ) {
			$response->setHeader( 'Cache-Control', 'public, must-revalidate, max-age=300' );
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function getETag() {
		$file = $this->resolveFile();
		if ( $file instanceof CacheableFile ) {
			return $file->getETag();
		}
		return null;
	}
}
