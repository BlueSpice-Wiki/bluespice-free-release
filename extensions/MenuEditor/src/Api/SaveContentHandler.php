<?php

namespace MediaWiki\Extension\MenuEditor\Api;

use MediaWiki\Context\RequestContext;
use MediaWiki\Rest\HttpException;
use Wikimedia\ParamValidator\ParamValidator;

class SaveContentHandler extends MenuHandler {

	/**
	 * @return \MediaWiki\Rest\Response|mixed
	 * @throws HttpException
	 */
	public function execute() {
		$params = $this->getValidatedParams();
		$page = $this->makeTitle( $params['pagename'] );
		$body = $this->getValidatedBody();

		$parser = $this->getParserForRevision( $page );
		$parser->addNodesFromData( $body['data'] );

		$rev = $parser->saveRevision( RequestContext::getMain()->getUser() );
		if ( !$rev ) {
			throw new HttpException( 'save-failed' );
		}
		return $this->getResponseFactory()->createJson( [
			'revision' => $rev->getId(),
		] );
	}

	/**
	 * @return array[]
	 */
	public function getParamSettings() {
		return [
			'pagename' => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_TYPE => 'string',
			]
		];
	}

	/**
	 * @return array[]
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
