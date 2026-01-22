<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\UserQueryStore;

use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Message\Message;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\UserFactory;
use MWStake\MediaWiki\Component\DataStore\ISecondaryDataProvider;

class SecondaryDataProvider implements ISecondaryDataProvider {
	/** @var UserFactory */
	protected $userFactory;
	/** @var LinkRenderer */
	protected $linkRenderer;
	/** @var TitleFactory */
	protected $titleFactory;

	/**
	 * @param UserFactory $userFactory
	 * @param LinkRenderer $linkRenderer
	 * @param TitleFactory $titleFactory
	 */
	public function __construct( $userFactory, $linkRenderer, $titleFactory ) {
		$this->userFactory = $userFactory;
		$this->linkRenderer = $linkRenderer;
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @param array $dataSets
	 *
	 * @return \MWStake\MediaWiki\Component\DataStore\Record[]
	 */
	public function extend( $dataSets ) {
		foreach ( $dataSets as $dataSet ) {
			$user = $this->userFactory->newFromId( $dataSet->get( UserRecord::ID ) );
			$userPage = $user->getUserPage();
			$userPageLink = $this->linkRenderer->makeLink(
				$userPage,
				// The whitespace is to aviod automatic rewrite to user_real_name by BSF
				$dataSet->get( 'display_name' ) . ' '
			);
			$dataSet->set( UserRecord::PAGE_LINK, $userPageLink );
			$dataSet->set( UserRecord::PAGE_URL, $userPage->getLocalURL() );
			$dataSet->set( UserRecord::PAGE_PREFIXED_TEXT, $userPage->getPrefixedText() );
		}

		return $dataSets;
	}
}
