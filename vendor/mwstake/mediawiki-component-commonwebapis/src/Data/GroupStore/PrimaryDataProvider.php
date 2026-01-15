<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\GroupStore;

use MediaWiki\Config\GlobalVarConfig;
use MWStake\MediaWiki\Component\DataStore\IPrimaryDataProvider;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use MWStake\MediaWiki\Component\Utils\Utility\GroupHelper;

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
	 * @param GroupHelper $groupHelper
	 * @param GlobalVarConfig $mwsgConfig
	 */
	public function __construct( GroupHelper $groupHelper, GlobalVarConfig $mwsgConfig ) {
		$this->groupHelper = $groupHelper;
		$this->mwsgConfig = $mwsgConfig;
	}

	/**
	 * @param ReaderParams $params
	 *
	 * @return array
	 */
	public function makeData( $params ) {
		$query = strtolower( $params->getQuery() );

		$data = [];
		$explicitGroups = $this->groupHelper->getAvailableGroups( [
			'filter' => $this->getGroupFilter(),
			'blacklist' => $this->mwsgConfig->get( 'CommonWebAPIsComponentGroupStoreExcludeGroups' ),
		] );
		foreach ( $explicitGroups as $group ) {
			$displayName = $group;
			$msg = \Message::newFromKey( "group-$group" );
			if ( $msg->exists() ) {
				$displayName = $msg->plain() . " ($group)";
			}

			if ( !$this->queryApplies( $query, $group, $displayName ) ) {
				continue;
			}

			$data[] = new GroupRecord( (object)[
				'group_name' => $group,
				'additional_group' => ( $this->groupHelper->getGroupType( $group ) === 'custom' ),
				'group_type' => $this->groupHelper->getGroupType( $group ),
				'displayname' => $displayName,
				'usercount' => $this->groupHelper->countUsersInGroup( $group, true, true )
			] );
		}
		return $data;
	}

	/**
	 * @return string[]
	 */
	protected function getGroupFilter(): array {
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
