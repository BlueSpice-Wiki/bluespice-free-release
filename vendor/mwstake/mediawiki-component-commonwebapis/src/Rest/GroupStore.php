<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Rest;

use MediaWiki\Config\GlobalVarConfig;
use MediaWiki\HookContainer\HookContainer;
use MWStake\MediaWiki\Component\CommonWebAPIs\Data\GroupStore\Store;
use MWStake\MediaWiki\Component\DataStore\IStore;
use MWStake\MediaWiki\Component\Utils\UtilityFactory;
use Wikimedia\ParamValidator\ParamValidator;

class GroupStore extends QueryStore {
	/** @var Store */
	private $store;

	/**
	 * @param HookContainer $hookContainer
	 * @param UtilityFactory $utilityFactory
	 * @param GlobalVarConfig $mwsgConfig
	 */
	public function __construct(
		HookContainer $hookContainer, UtilityFactory $utilityFactory, GlobalVarConfig $mwsgConfig
	) {
		parent::__construct( $hookContainer );
		$this->store = new Store( $utilityFactory, $mwsgConfig, $hookContainer );
	}

	/**
	 * @return IStore
	 */
	protected function getStore(): IStore {
		$allowEveryone = $this->getValidatedParams()['allowEveryone'] ?? false;
		$this->store->setAllowEveryone( $allowEveryone );
		return $this->store;
	}

	/**
	 * @return array|array[]
	 */
	public function getStoreSpecificParams(): array {
		return [
			'allowEveryone' => [
				static::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_TYPE => 'boolean',
				ParamValidator::PARAM_DEFAULT => false
			],
		];
	}
}
