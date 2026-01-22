<?php

namespace MWStake\MediaWiki\Component\TokenAuthenticator;

use MediaWiki\Context\RequestContext;
use MediaWiki\Rest\HttpException;
use MediaWiki\Rest\SimpleHandler;
use Wikimedia\ParamValidator\ParamValidator;

class GenerateTokenHandler extends SimpleHandler {

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
		$user = RequestContext::getMain()->getUser();
		$withIssuer = $this->getValidatedParams()['withIssuer'];
		return $withIssuer ?
			$this->userTokenAuthenticator->generateTokenWithIssuer( $user ) :
			$this->userTokenAuthenticator->generateToken( $user );
	}

	/**
	 * @return array[]
	 */
	public function getParamSettings() {
		return [
			'withIssuer' => [
				static::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_TYPE => 'boolean',
				ParamValidator::PARAM_DEFAULT => false,
			]
		];
	}

	public function needsReadAccess() {
		return true;
	}
}
