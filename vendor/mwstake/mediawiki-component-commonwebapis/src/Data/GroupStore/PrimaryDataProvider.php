<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\GroupStore;

use MediaWiki\Config\GlobalVarConfig;
use MediaWiki\HookContainer\HookContainer;
use MWStake\MediaWiki\Component\DataStore\IPrimaryDataProvider;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use MWStake\MediaWiki\Component\Utils\Utility\GroupHelper;

/*
 * @stable to extend
 */
class PrimaryDataProvider implements IPrimaryDataProvider {
	/**
	 * @var GroupHelper
	 */
	protected $groupHelper;

	/**
	 * @var GlobalVarConfig
	 */
	protected $mwsgConfig;

	/**
	 * @var HookContainer
	 */
	protected $hookContainer;

	/** @var bool */
	private bool $allowEveryone;

	/**
	 * @param GroupHelper $groupHelper
	 * @param GlobalVarConfig $mwsgConfig
	 * @param HookContainer $hookContainer
	 * @param bool $allowEveryone
	 */
	public function __construct(
		GroupHelper $groupHelper, GlobalVarConfig $mwsgConfig, HookContainer $hookContainer, bool $allowEveryone = false
	) {
		$this->groupHelper = $groupHelper;
		$this->mwsgConfig = $mwsgConfig;
		$this->hookContainer = $hookContainer;
		$this->allowEveryone = $allowEveryone;
	}

	/**
	 * @param ReaderParams $params
	 *
	 * @return array
	 */
	public function makeData( $params ) {
		$query = strtolower( $params->getQuery() );

		$data = [];
		$typeFilter = $this->getGroupTypeFilter( $params );
		$this->hookContainer->run( 'MWStakeGroupStoreGroupTypeFilter', [ &$typeFilter ] );
		$explicitGroups = $this->groupHelper->getAvailableGroups( [
			'filter' => $typeFilter,
			'blacklist' => $this->mwsgConfig->get( 'CommonWebAPIsComponentGroupStoreExcludeGroups' ),
		] );
		if ( $this->allowEveryone ) {
			array_unshift( $explicitGroups, 'user' );
		}
		foreach ( $explicitGroups as $group ) {
			$groupType = $this->groupHelper->getGroupType( $group );
			$displayName = $group;
			$msg = \Message::newFromKey( "group-$group" );
			if ( $msg->exists() ) {
				$displayName = $msg->plain() . " ($group)";
			}
			$this->hookContainer->run( 'MWStakeGroupStoreGroupDisplayName', [ $group, &$displayName, $groupType ] );

			if ( !$this->queryApplies( $query, $group, $displayName ) ) {
				continue;
			}

			$data[] = new GroupRecord( (object)[
				'group_name' => $group,
				'additional_group' => ( $groupType === 'custom' ),
				'group_type' => $groupType,
				'displayname' => $displayName,
				'usercount' => $this->groupHelper->countUsersInGroup( $group, true, true )
			] );
		}
		return $data;
	}

	/**
	 * @return string[]
	 */
	protected function getGroupTypeFilter( ReaderParams $params ): array {
		$filters = $params->getFilter();
		foreach ( $filters as $filter ) {
			if ( $filter->getField() === 'group_type' ) {
				$filter->setApplied();
				switch ( $filter->getComparison() ) {
					case 'eq':
						return [ $filter->getValue() ];
					case 'in':
						if ( is_array( $filter->getValue() ) ) {
							return $filter->getValue();
						}
						return [ $filter->getValue() ];
				}
			}
		}
		return [ 'explicit' ];
	}

	/**
	 * @param string $query
	 * @param string $group
	 * @param string $displayName
	 *
	 * @return bool
	 */
	private function queryApplies( $query, $group, $displayName ): bool {
		if ( $query === '' ) {
			return true;
		}
		$query = mb_strtolower( $query );
		if ( strpos( mb_strtolower( $group ), $query ) !== false ) {
			return true;
		}
		if ( strpos( mb_strtolower( $displayName ), $query ) !== false ) {
			return true;
		}

		return false;
	}
}
