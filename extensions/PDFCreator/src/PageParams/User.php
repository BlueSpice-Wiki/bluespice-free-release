<?php

namespace MediaWiki\Extension\PDFCreator\PageParams;

use MediaWiki\Extension\PDFCreator\IPageParamsProvider;
use MediaWiki\Extension\PDFCreator\Utility\ParamDesc;
use MediaWiki\Message\Message;
use MediaWiki\Page\PageIdentity;
use MediaWiki\User\UserFactory;
use MediaWiki\User\UserIdentity;

class User implements IPageParamsProvider {

	/** @var UserFactory */
	private $userFactory;

	/**
	 * @param UserFactory $userFactory
	 */
	public function __construct( UserFactory $userFactory ) {
		$this->userFactory = $userFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function execute( ?PageIdentity $pageIdentity, ?UserIdentity $userIdentity ): array {
		if ( $userIdentity === null ) {
			return [];
		}

		$user = $this->userFactory->newFromUserIdentity( $userIdentity );
		if ( !$user ) {
			return [];
		}

		return [
			'username' => $user->getName(),
			'user-realname' => $user->getRealName()
		];
	}

	/**
	 * @param string $language
	 * @return ParamDesc[]
	 */
	public function getParamsDescription( $language ): array {
		return [
			new ParamDesc(
				'username',
				Message::newFromKey( 'pdfcreator-pageparam-username' )
			),
			new ParamDesc(
				'user-realname',
				Message::newFromKey( 'pdfcreator-pageparam-user-realname' )
			)
		];
	}
}
