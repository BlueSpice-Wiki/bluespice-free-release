<?php

namespace BlueSpice\SaferEdit\Rest;

use MediaWiki\Message\Message;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentity;
use Wikimedia\ParamValidator\ParamValidator;

class StartEditHandler extends SaferEditEventHandler {

	/**
	 * @return array[]
	 */
	public function getBodyParamSettings(): array {
		return parent::getBodyParamSettings() + [
			'section' => [
				static::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
			],
		];
	}

	/**
	 * @param Title $title
	 * @param UserIdentity $user
	 * @param array $body
	 * @return void
	 */
	protected function process( Title $title, UserIdentity $user, array $body ): void {
		$this->logger->debug( 'Starting editing on page {page} for user {user}',
			[
				'page' => $title->getPrefixedText(),
				'user' => $user->getName(),
			]
		);
		$status = $this->saferEditManager->saveUserEditing( $user, $title, $body['section'] ?? -1 );

		if ( !$status->isOK() ) {
			$this->logger->error(
				'Failed to start editing on page {page} for user {user}: {error}',
				[
					'page' => $title->getPrefixedText(),
					'user' => $user->getName(),
					'error' => Message::newFromSpecifier( $status->getMessages()[0] )->text(),
				]
			);
		}
	}
}
