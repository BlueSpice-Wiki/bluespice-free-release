<?php

namespace MWStake\MediaWiki\Component\DynamicFileDispatcher\Rest;

use MediaWiki\Permissions\Authority;
use MediaWiki\Rest\HttpException;
use MediaWiki\Rest\SimpleHandler;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\DynamicFileDispatcherFactory;
use MWStake\MediaWiki\Component\DynamicFileDispatcher\IDynamicFileModule;
use Wikimedia\ParamValidator\ParamValidator;

class Handler extends SimpleHandler {

	/**
	 * @var DynamicFileDispatcherFactory
	 */
	private $moduleFactory;

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
		$module = $this->getDynamicModule();
		if ( !$module ) {
			throw new HttpException( 'Module not found', 404 );
		}
		$queryParams = $this->getRequest()->getQueryParams();
		$authority = \RequestContext::getMain()->getUser();
		if ( !( $authority instanceof Authority ) ) {
			throw new HttpException( 'Unauthorized', 403 );
		}
		if ( !$module->isAuthorized( $authority, $queryParams ) ) {
			throw new HttpException( 'Unauthorized', 403 );
		}
		$file = $module->getFile( $queryParams );
		if ( !$file ) {
			throw new HttpException( 'File not found', 404 );
		}

		$response = $this->getResponseFactory()->create();
		$response->setHeader( 'Content-Type', $file->getMimeType() );
		$response->setBody( $file->getStream() );

		return $response;
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
}
