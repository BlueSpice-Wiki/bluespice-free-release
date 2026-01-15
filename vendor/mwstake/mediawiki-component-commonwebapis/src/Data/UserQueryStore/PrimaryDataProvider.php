<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\UserQueryStore;

use MediaWiki\Config\GlobalVarConfig;
use MediaWiki\Message\Message;
use MediaWiki\Parser\Sanitizer;
use MWStake\MediaWiki\Component\DataStore\Filter;
use MWStake\MediaWiki\Component\DataStore\PrimaryDatabaseDataProvider;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use MWStake\MediaWiki\Component\DataStore\Schema;
use Wikimedia\Rdbms\IDatabase;

class PrimaryDataProvider extends PrimaryDatabaseDataProvider {
	/** @var array */
	private $groupLabels = [];
	/** @var array */
	private $blocks = [];

	/** @var GlobalVarConfig */
	protected $mwsgConfig;

	/**
	 * @param IDatabase $db
	 * @param Schema $schema
	 * @param GlobalVarConfig $mwsgConfig
	 */
	public function __construct( IDatabase $db, Schema $schema, GlobalVarConfig $mwsgConfig ) {
		parent::__construct( $db, $schema );
		$this->mwsgConfig = $mwsgConfig;
	}

	/**
	 * @inheritDoc
	 */
	public function makeData( $params ) {
		$this->getSupportingData( $params );
		return parent::makeData( $params );
	}

	/**
	 * Get supporting data for the user records
	 *
	 * @param ReaderParams $params
	 * @return void
	 */
	private function getSupportingData( ReaderParams $params ) {
		$this->blocks = $this->getBlocks();
	}

	/**
	 * @return array
	 */
	public function getGroupBuckets(): array {
		$groupBlacklist = $this->mwsgConfig->get( 'CommonWebAPIsComponentUserStoreExcludeGroups' );
		$res = $this->db->select(
			'user_groups',
			[ 'DISTINCT ug_group' ],
			[
				'ug_group NOT IN (' . $this->db->makeList( $groupBlacklist ) . ')',
			],
			__METHOD__,
			[ 'GROUP BY' => 'ug_group' ]
		);
		$groups = [];
		foreach ( $res as $row ) {
			$groups[$row->ug_group] = $this->tryGetGroupLabel( $row->ug_group );
		}

		return $groups;
	}

	/**
	 * @return array
	 */
	private function getBlocks() {
		$res = $this->db->select(
			[ 'b' => 'block', 'bt' => 'block_target' ],
			[ 'bt_user' ],
			[],
			__METHOD__,
			[],
			[ 'b' => [ 'INNER JOIN', 'bt.bt_id = b.bl_target' ] ]
		);
		$blocks = [];
		foreach ( $res as $row ) {
			$blocks[] = (int)$row->bt_user;
		}

		return $blocks;
	}

	/**
	 * @param ReaderParams $params
	 *
	 * @return array
	 */
	protected function makePreFilterConds( ReaderParams $params ) {
		$filters = $params->getFilter();
		$conds = parent::makePreFilterConds( $params );
		$query = $params->getQuery();
		$hasGroupConditions = false;
		foreach ( $filters as $filter ) {
			if ( $filter->getField() === 'user_name' || $filter->getField() == 'user_real_name' ) {
				// Incompatible with query, takes priority
				$query = '';
				$this->addIndexedNameFilter( $filter, $conds );
				$filter->setApplied( true );
			}
			if ( $filter->getField() === 'groups' ) {
				$filterValue = $filter->getValue();
				$cond = $this->getGroupCondition( $filterValue );
				if ( $cond ) {
					$conds[] = $cond;
				}
				$filter->setApplied( true );
				$hasGroupConditions = true;
			}
		}
		if ( $query !== '' ) {
			$query = mb_strtolower( $query );
			$conds[] = $this->db->makeList(
				[
					'mui_user_name ' . $this->db->buildLike(
						$this->db->anyString(), $query, $this->db->anyString()
					),
					'mui_user_real_name ' . $this->db->buildLike(
						$this->db->anyString(), $query, $this->db->anyString()
					)
				],
				LIST_OR
			);
		}
		// General system user identifier
		$conds[] = 'user_token NOT ' . $this->db->buildLike(
			$this->db->anyString(), 'INVALID', $this->db->anyString()
		);

		$groupBlacklist = $this->mwsgConfig->get( 'CommonWebAPIsComponentUserStoreExcludeGroups' );
		if ( is_array( $groupBlacklist ) && count( $groupBlacklist ) > 0 ) {
			if ( $hasGroupConditions ) {
				// Positive list already set, we dont need to return users with no groups
				$conds[] = 'ug_group NOT IN (' . $this->db->makeList( $groupBlacklist ) . ')';
			} else {
				$conds[] = '(' .
					$this->db->makeList(
						[
							'ug_group NOT IN (' . $this->db->makeList( $groupBlacklist ) . ')',
							'ug_group IS NULL'
						],
						LIST_OR
					) . ')';
			}

		}
		$userBlacklist = $this->mwsgConfig->get( 'CommonWebAPIsComponentUserStoreExcludeUsers' );
		if ( is_array( $userBlacklist ) && count( $userBlacklist ) > 0 ) {
			$conds[] = 'user_name NOT IN (' . $this->db->makeList( $userBlacklist ) . ')';
		}

		return $conds;
	}

	/**
	 * @param Filter $filter
	 * @param array &$conds
	 * @return void
	 */
	private function addIndexedNameFilter( Filter $filter, array &$conds ) {
		$fieldMapping = [
			'user_name' => 'mui_user_name',
			'user_real_name' => 'mui_user_real_name'
		];
		$filterValue = str_replace( '_', ' ', $filter->getValue() );
		$comparison = $filter->getComparison();
		$field = $filter->getField();

		if (
			$comparison === Filter::COMPARISON_CONTAINS ||
			$comparison === Filter::COMPARISON_LIKE
		) {
			$filterValue = mb_strtolower( $filterValue );
			$field = $fieldMapping[$field];
			$conds[] = "$field " . $this->db->buildLike(
				$this->db->anyString(), $filterValue, $this->db->anyString()
			);
		} elseif (
			$comparison === Filter::COMPARISON_EQUALS ||
			$comparison === Filter::COMPARISON_NOT_EQUALS
		) {
			$conds[$field] = $filterValue;
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function skipPreFilter( Filter $filter ) {
		return $filter->getField() === 'user_name' ||
			$filter->getField() === 'user_real_name' ||
			$filter->getField() === 'enabled';
	}

	/**
	 * @param int $userId
	 *
	 * @return bool
	 */
	protected function isUserBlocked( int $userId ) {
		return in_array( (int)$userId, $this->blocks );
	}

	/**
	 * @return string[]
	 */
	protected function getFields() {
		return [
			'user_id', 'user_name', 'user_real_name', 'user_registration', 'user_editcount', 'user_email',
			'GROUP_CONCAT( ug_group SEPARATOR \'|\' ) AS \'groups\'',
		];
	}

	/**
	 * @param \stdClass $row
	 *
	 * @return void
	 */
	protected function appendRowToData( \stdClass $row ) {
		$realName = $row->user_real_name !== null ? Sanitizer::stripAllTags( $row->user_real_name ?? '' ) : null;
		$resultRow = [
			'user_id' => (int)$row->user_id,
			'user_name' => $row->user_name,
			'user_real_name' => $realName,
			'user_registration' => $row->user_registration,
			'user_editcount' => (int)$row->user_editcount,
			'user_email' => $row->user_email,
			'groups' => $this->setGroupLabels( explode( '|', $row->groups ) ),
			'groups_raw' => explode( '|', $row->groups ),
			'enabled' => !$this->isUserBlocked( (int)$row->user_id ),
			// legacy fields
			'display_name' => !$realName ? $row->user_name : $realName,
		];
		$this->data[] = new UserRecord( (object)$resultRow );
	}

	/**
	 * @inheritDoc
	 */
	protected function getJoinConds( ReaderParams $params ) {
		return [
			'mws_user_index' => [
				'INNER JOIN', [ 'user_id = mui_user_id' ]
			],
			'user_groups' => [
				'LEFT OUTER JOIN', [ 'ug_user = user_id' ],
			]
		];
	}

	/**
	 * @return string[]
	 */
	protected function getTableNames() {
		return [ 'user', 'mws_user_index', 'user_groups' ];
	}

	/**
	 * @param mixed $filterValue
	 * @return string|void
	 */
	private function getGroupCondition( $filterValue ) {
		if ( is_array( $filterValue ) && count( $filterValue ) > 0 ) {
			$groupConds = [];
			foreach ( $filterValue as &$value ) {
				$groupConds[] = 'ug_group = ' . $this->db->addQuotes( $value );
			}
			return $this->db->makeList(
				$groupConds,
				LIST_OR
			);
		} elseif ( is_string( $filterValue ) && $filterValue !== '' ) {
			return 'ug_group = ' . $this->db->addQuotes( $filterValue );
		}

		return null;
	}

	/**
	 * @param ReaderParams $params
	 * @return array
	 */
	protected function makePreOptionConds( ReaderParams $params ) {
		$options = parent::makePreOptionConds( $params );
		$options['GROUP BY'] = 'user_id';
		return $options;
	}

	/**
	 * @param array $groups
	 * @return string[]
	 */
	private function setGroupLabels( array $groups ): array {
		return array_filter( array_map(
			function ( $group ) {
				if ( empty( $group ) ) {
					return null;
				}
				return $this->tryGetGroupLabel( $group );
			},
			$groups
		) );
	}

	/**
	 * @param string $group
	 * @return string
	 */
	private function tryGetGroupLabel( string $group ): string {
		if ( !isset( $this->groupLabels[$group] ) ) {
			$msg = Message::newFromKey( 'group-' . $group );
			$this->groupLabels[$group] = $msg->exists() ? $msg->text() : $group;
		}

		return $this->groupLabels[$group];
	}
}
