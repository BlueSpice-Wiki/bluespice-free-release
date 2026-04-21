<?php

namespace MWStake\MediaWiki\Component\TokenAuthenticator\Rest;

use MediaWiki\Rest\HttpException;
use MediaWiki\Rest\SimpleHandler;
use MWStake\MediaWiki\Component\TokenAuthenticator\AppTokenAuthenticator;
use Wikimedia\ParamValidator\ParamValidator;

class VerifyAppTokenHandler extends SimpleHandler {

	/**
	 * @param AppTokenAuthenticator $appTokenAuthenticator
	 */
	public function __construct(
		private readonly AppTokenAuthenticator $appTokenAuthenticator
	) {
	}

	/**
	 * @return \MediaWiki\Rest\Response|mixed
	 * @throws HttpException
	 */
	public function execute() {
		$params = $this->getValidatedParams();
		$data = $this->appTokenAuthenticator->doVerifyToken( $params['token'] );
		if ( !$data ) {
			throw new HttpException(
				'Invalid or expired token.',
				400
			);
		}
		return $this->getResponseFactory()->createJson( $data );
	}

	/**
	 * @return array[]
	 */
	public function getParamSettings() {
		return [
			'token' => [
				static::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			]
		];
	}

	/**
	 * @return false
	 */
	public function needsReadAccess() {
		return false;
	}
}
