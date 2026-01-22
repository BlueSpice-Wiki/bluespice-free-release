<?php

namespace MWStake\MediaWiki\Component\Wire\Rest;

use MediaWiki\Context\RequestContext;
use MediaWiki\Rest\SimpleHandler;
use MWStake\MediaWiki\Component\Wire\WireChannelAuthorizer;
use Wikimedia\ParamValidator\ParamValidator;

class CheckCanSubscribeHandler extends SimpleHandler {

	/**
	 * @param WireChannelAuthorizer $channelAuthorizer
	 */
	public function __construct( private readonly WireChannelAuthorizer $channelAuthorizer ) {
	}

	/**
	 * @return true
	 */
	public function execute() {
		$body = $this->getValidatedBody();
		return $this->channelAuthorizer->canUserSubscribeToChannel(
			$body['channel'], RequestContext::getMain()->getUser()
		);
	}

	/**
	 * @return array[]
	 */
	public function getBodyParamSettings(): array {
		return [
			'channel' => [
				static::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			]
		];
	}
}
