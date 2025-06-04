<?php

namespace MediaWiki\Extension\UserProfile\Rest;

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\UserProfile\ProfileManager;
use MediaWiki\Message\Message;
use MediaWiki\Rest\HttpException;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\User\User;
use MediaWiki\User\UserFactory;
use Wikimedia\ParamValidator\ParamValidator;

class RetrieveProfileDataHandler extends SimpleHandler {

	/** @var ProfileManager */
	protected $manager;

	/** @var UserFactory */
	protected $userFactory;

	/**
	 * @param ProfileManager $manager
	 * @param UserFactory $userFactory
	 */
	public function __construct( ProfileManager $manager, UserFactory $userFactory ) {
		$this->manager = $manager;
		$this->userFactory = $userFactory;
	}

	public function execute() {
		$user = $this->getValidatedTargetUser();
		$requester = $this->getValidatedRequester();
		return $this->getResponseFactory()->createJson( [
			'data' => $this->manager->getProfileData( $user, $requester ),
			'fields' => $this->manager->getFieldRegistry()->getSerializedFields(
				$user, $requester, RequestContext::getMain()->getLanguage()
			),
		] );
	}

	/**
	 * @return array[]
	 */
	public function getParamSettings() {
		return [
			'user' => [
				static::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true
			],
		];
	}

	/**
	 * @return User
	 * @throws HttpException
	 */
	protected function getValidatedTargetUser(): User {
		$params = $this->getValidatedParams();
		$user = $this->userFactory->newFromName( $params['user'] );
		if ( !$user || !$user->isRegistered() ) {
			throw new HttpException( Message::newFromKey( 'userprofile-user-not-found' )->text(), 404 );
		}
		return $user;
	}

	/**
	 * @return User
	 * @throws HttpException
	 */
	protected function getValidatedRequester(): User {
		$requester = RequestContext::getMain()->getUser();
		if ( !$requester ) {
			throw new HttpException( Message::newFromKey( 'userprofile-unauthorized' )->text(), 401 );
		}
		return $requester;
	}
}
