<?php

namespace BlueSpice\SaferEdit\Rest;

use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentity;

class StopEditHandler extends SaferEditEventHandler {

	/**
	 * @param Title $title
	 * @param UserIdentity $user
	 * @param array $body
	 * @return void
	 */
	protected function process( Title $title, UserIdentity $user, array $body ): void {
		$this->logger->debug( 'Stopping editing on page {page} for user {user}',
			[
				'page' => $title->getPrefixedText(),
				'user' => $user->getName(),
			]
		);
		$this->saferEditManager->doClearSaferEdit( $user, $title );
	}
}
