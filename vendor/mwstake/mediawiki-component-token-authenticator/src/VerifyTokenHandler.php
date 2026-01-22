<?php

namespace MWStake\MediaWiki\Component\TokenAuthenticator;

use MediaWiki\Rest\HttpException;
use MediaWiki\Rest\SimpleHandler;
use Wikimedia\ParamValidator\ParamValidator;

class VerifyTokenHandler extends SimpleHandler {

	/**
	 * @param UserTokenAuthenticator $userTokenAuthenticator
	 */
	public function __construct(
		private readonly UserTokenAuthenticator $userTokenAuthenticator
	) {
	}

	/**
	 * @return \MediaWiki\Rest\Response|mixed
	 * @throws HttpException
	 */
	public function execute() {
		$params = $this->getValidatedParams();
		$user = $this->userTokenAuthenticator->verifyToken( $params['token'] );
		if ( !$user ) {
			throw new HttpException(
				'Invalid or expired token.',
				400
			);
		}
		$info = $this->userTokenAuthenticator->getAuthInfo( $user );
		if ( !$info ) {
			throw new HttpException(
				'Invalid or expired token.',
				400
			);
		}
		return $this->getResponseFactory()->createJson( $info->jsonSerialize() );
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
