<?php

namespace MediaWiki\Extension\UserProfile\Rest;

use MediaWiki\Rest\HttpException;
use MediaWiki\Rest\Response;
use Throwable;
use Wikimedia\ParamValidator\ParamValidator;

class SaveProfileDataHandler extends RetrieveProfileDataHandler {

	/**
	 * @return Response
	 * @throws HttpException
	 */
	public function execute() {
		$user = $this->getValidatedTargetUser();
		$requester = $this->getValidatedRequester();
		$body = $this->getValidatedBody();
		try {
			$this->manager->setProfileData( $body['data'], $user, $requester );
		} catch ( Throwable $e ) {
			throw new HttpException( $e->getMessage(), 500 );
		}
		return $this->getResponseFactory()->createJson( [
			'success' => true,
		] );
	}

	/**
	 * @return array[]
	 */
	public function getBodyParamSettings(): array {
		return [
			'data' => [
				static::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'array',
				ParamValidator::PARAM_REQUIRED => true,
			]
		];
	}
}
