<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Rest;

use MediaWiki\Config\GlobalVarConfig;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\UserFactory;
use MWStake\MediaWiki\Component\CommonWebAPIs\Data\UserQueryStore\Store;
use MWStake\MediaWiki\Component\DataStore\IStore;
use MWStake\MediaWiki\Component\Utils\UtilityFactory;
use Wikimedia\Rdbms\ILoadBalancer;

class UserQueryStore extends QueryStore {
	/** @var Store */
	private $store;

	/**
	 * @param HookContainer $hookContainer
	 * @param ILoadBalancer $lb
	 * @param UserFactory $userFactory
	 * @param LinkRenderer $linkRenderer
	 * @param TitleFactory $titleFactory
	 * @param GlobalVarConfig $mwsgConfig
	 * @param UtilityFactory|null $utilityFactory
	 */
	public function __construct(
		HookContainer $hookContainer, ILoadBalancer $lb, UserFactory $userFactory,
		LinkRenderer $linkRenderer, TitleFactory $titleFactory, GlobalVarConfig $mwsgConfig,
		?UtilityFactory $utilityFactory = null
	) {
		parent::__construct( $hookContainer );
		if ( !$utilityFactory ) {
			$utilityFactory = MediaWikiServices::getInstance()->getService( 'MWStakeCommonUtilsFactory' );
		}
		$this->store = new Store( $lb, $userFactory, $linkRenderer, $titleFactory, $mwsgConfig, $utilityFactory );
	}

	/**
	 * @return IStore
	 */
	protected function getStore(): IStore {
		return $this->store;
	}
}
