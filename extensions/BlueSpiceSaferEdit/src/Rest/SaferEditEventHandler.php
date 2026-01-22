<?php

namespace BlueSpice\SaferEdit\Rest;

use BlueSpice\SaferEdit\SaferEditManager;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\UserIdentity;
use Psr\Log\LoggerInterface;
use RequestContext;
use Wikimedia\ParamValidator\ParamValidator;

abstract class SaferEditEventHandler extends SimpleHandler {

	/** @var LoggerInterface */
	protected LoggerInterface $logger;

	/**
	 * @param SaferEditManager $saferEditManager
	 * @param TitleFactory $titleFactory
	 */
	public function __construct(
		protected readonly SaferEditManager $saferEditManager,
		private readonly TitleFactory $titleFactory
	) {
		$this->logger = LoggerFactory::getInstance( 'BlueSpiceSaferEdit' );
	}

	/**
	 * @return true
	 */
	public function execute() {
		$body = $this->getValidatedBody();
		$title = $this->titleFactory->newFromText( $body['page'] );
		$this->saferEditManager->askEnvironmentalCheckers( 'getEditedTitle', $title );
		if ( !$title ) {
			$this->logger->error( 'Tried to start editing on an invalid page title: {title}', [
				'title' => $body['page'] ?? 'unknown',
			] );
			return true;
		}
		$user = RequestContext::getMain()->getUser();
		$this->process( $title, $user, $body );
		return true;
	}

	/**
	 * @param Title $title
	 * @param UserIdentity $user
	 * @param array $body
	 * @return void
	 */
	abstract protected function process( Title $title, UserIdentity $user, array $body ): void;

	/**
	 * @return array[]
	 */
	public function getBodyParamSettings(): array {
		return [
			'page' => [
				static::PARAM_SOURCE => 'body',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
		];
	}
}
