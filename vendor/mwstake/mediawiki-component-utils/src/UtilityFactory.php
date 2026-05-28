<?php

namespace MWStake\MediaWiki\Component\Utils;

use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\Utils\Utility\GroupHelper;

/**
 * UtilityFactory class for MWStake components
 */
class UtilityFactory {

	/** @var MediaWikiServices */
	protected $services;

	/** @var array */
	protected $instances = [];

	/**
	 * @param MediaWikiServices $services
	 */
	public function __construct( MediaWikiServices $services ) {
		$this->services = $services;
	}

	/**
	 * @return GroupHelper
	 */
	public function getGroupHelper() {
		$groupManager = $this->services->getUserGroupManager();
		$config = $this->services->getMainConfig();
		$additionalGroups = $config->get( 'AdditionalGroups' );
		$groupTypes = $config->get( 'GroupTypes' );
		$dbr = $this->services->getDBLoadBalancer()->getConnection( DB_REPLICA );

		return new GroupHelper(
			$groupManager, $additionalGroups, $groupTypes, $dbr, $this->services->getUserFactory()
		);
	}

	/**
	 * @return ReadableNamespaces
	 */
	public function getReadableNamespacesHelper(): ReadableNamespaces {
		if ( !isset( $this->instances['readableNamespaces'] ) ) {
			$this->instances['readableNamespaces'] = new ReadableNamespaces(
				$this->services->getNamespaceInfo(),
				$this->services->getMainConfig(),
				$this->services->getHookContainer(),
				$this->services->getUserGroupManager()
			);
		}
		return $this->instances['readableNamespaces'];
	}

	/**
	 * @return MessageHelper
	 */
	public function getMessageHelper(): MessageHelper {
		if ( !isset( $this->instances['messageHelper'] ) ) {
			$this->instances['messageHelper'] = new MessageHelper(
				$this->services->getLocalisationCache(),
				$this->services->getDBLoadBalancer(),
				$this->services->getContentLanguageCode()
			);
		}
		return $this->instances['messageHelper'];
	}

	/**
	 * @return DisplayTitleHelper
	 */
	public function getDisplayTitleHelper(): DisplayTitleHelper {
		if ( !isset( $this->instances['displayTitleHelper'] ) ) {
			$this->instances['displayTitleHelper'] = new DisplayTitleHelper(
				$this->services->getObjectCacheFactory(),
				$this->services->getDBLoadBalancer()
			);
		}
		return $this->instances['displayTitleHelper'];
	}
}
