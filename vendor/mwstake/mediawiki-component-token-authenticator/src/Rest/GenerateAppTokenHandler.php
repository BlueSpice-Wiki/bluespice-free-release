<?php

namespace MWStake\MediaWiki\Component\TokenAuthenticator\Rest;

use MediaWiki\Context\RequestContext;
use MediaWiki\Rest\HttpException;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use MWStake\MediaWiki\Component\TokenAuthenticator\AppTokenAuthenticator;
use MWStake\MediaWiki\Component\TokenAuthenticator\CIDRValidator;
use Random\RandomException;

class GenerateAppTokenHandler extends SimpleHandler {

	/**
	 * @param AppTokenAuthenticator $tokenAuthenticator
	 * @param CIDRValidator $CIDRValidator
	 */
	public function __construct(
		private readonly AppTokenAuthenticator $tokenAuthenticator,
		private readonly CIDRValidator $CIDRValidator
	) {
	}

	/**
	 * @return Response|mixed
	 * @throws HttpException
	 * @throws RandomException
	 */
	public function execute() {
		if ( !$this->CIDRValidator->validateIP( RequestContext::getMain()->getRequest()->getIP() ) ) {
			throw new HttpException( 403, 'Forbidden' );
		}
		return $this->tokenAuthenticator->generateToken();
	}

	public function needsReadAccess() {
		return true;
	}
}
