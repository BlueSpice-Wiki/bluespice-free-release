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
	 */
	public function __construct(
		private readonly AppTokenAuthenticator $tokenAuthenticator
	) {
	}

	/**
	 * @return Response|mixed
	 * @throws HttpException
	 * @throws RandomException
	 */
	public function execute() {
		$cidrValidator = new CIDRValidator();
		$clientIP = RequestContext::getMain()->getRequest()->getIP();
		$cidr = $GLOBALS['mwsgTokenAuthenticatorServiceCIDR'] ?? '';
		if ( !CIDRValidator::isValidCIDR( $cidr ) ) {
			// Fail closed: CIDR restriction must never be empty or invalid
			throw new HttpException( 403, 'Forbidden' );
		}
		if ( !$cidrValidator->validateIP( $clientIP, $cidr ) ) {
			throw new HttpException( 403, 'Forbidden' );
		}
		return $this->tokenAuthenticator->generateToken();
	}

	/**
	 * @inheritDoc
	 */
	public function needsReadAccess() {
		return true;
	}
}
