<?php

namespace BlueSpice\SaferEdit\Rest;

use BlueSpice\SaferEdit\KeepAliveWatcher;
use BlueSpice\SaferEdit\SaferEditManager;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\UserIdentity;
use Wikimedia\Rdbms\ILoadBalancer;

class KeepAliveHandler extends SaferEditEventHandler {

	/**
	 * @param SaferEditManager $saferEditManager
	 * @param TitleFactory $titleFactory
	 * @param ILoadBalancer $lb
	 */
	public function __construct(
		SaferEditManager $saferEditManager, TitleFactory $titleFactory,
		private readonly ILoadBalancer $lb
	) {
		parent::__construct( $saferEditManager, $titleFactory );
	}

	/**
	 * @param Title $title
	 * @param UserIdentity $user
	 * @param array $body
	 * @return void
	 */
	protected function process( Title $title, UserIdentity $user, array $body ): void {
		$this->logger->debug( 'Keep-alive signal on page {page} for user {user}',
			[
				'page' => $title->getPrefixedText(),
				'user' => $user->getName(),
			]
		);
		$watcher = new KeepAliveWatcher( $this->lb );
		$watcher->updateTimestamp( $title, $user->getUser() );
	}
}
