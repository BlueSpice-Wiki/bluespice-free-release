<?php

namespace MWStake\MediaWiki\Component\Wire\Rest;

use MediaWiki\Rest\SimpleHandler;
use MWStake\MediaWiki\Component\Wire\WireChannel;
use MWStake\MediaWiki\Component\Wire\WireMessage;
use MWStake\MediaWiki\Component\Wire\WireMessenger;
use Wikimedia\ParamValidator\ParamValidator;

class SendMessageHandler extends SimpleHandler {

	/**
	 * @param WireMessenger $messenger
	 */
	public function __construct( private readonly WireMessenger $messenger ) {
	}

	/**
	 * @return true
	 */
	public function execute() {
		$body = $this->getValidatedBody();
		$message = new WireMessage(
			new WireChannel( $body['channel'] ),
			$body['payload'] ?? null
		);

		$this->messenger->send( $message );
		return true;
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
			],
			'payload' => [
				static::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'array',
				ParamValidator::PARAM_REQUIRED => false,
			],
		];
	}

	/**
	 * @return bool
	 */
	public function needsWriteAccess() {
		return parent::needsWriteAccess();
	}
}
