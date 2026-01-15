<?php

namespace MediaWiki\Extension\NotifyMe\MediaWiki\Data\WebNotifications;

use MediaWiki\Extension\NotifyMe\Storage\WebNotificationQueryStore;
use MediaWiki\User\UserIdentity;
use MWStake\MediaWiki\Component\DataStore\Filter;
use MWStake\MediaWiki\Component\DataStore\PrimaryDatabaseDataProvider;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use Wikimedia\Rdbms\IDatabase;

class PrimaryDataProvider extends PrimaryDatabaseDataProvider {

	/**
	 * @var WebNotificationQueryStore
	 */
	private $queryStore;

	/**
	 * @var UserIdentity
	 */
	private $forUser;
	/** @var array */
	private $fields = [
		WebNotificationRecord::ID => 'nwqs_notification_id',
		WebNotificationRecord::STATUS => 'nwqs_status',
		WebNotificationRecord::NAMESPACE_ID => 'nwqs_namespace_id',
		WebNotificationRecord::NAMESPACE_TEXT => 'nwqs_namespace_text',
		WebNotificationRecord::BUCKETS => 'nwqs_buckets',
		WebNotificationRecord::CATEGORIES => 'nwqs_categories',
		WebNotificationRecord::TIMESTAMP => 'nwqs_notification_timestamp',
		// Non-schema fields
		'title' => 'nwqs_title',
		'namespaces' => 'nwqs_namespace_id',
		'event-type' => 'ni_event_type'
	];

	/** @var string[] */
	private $nonSchemaFields = [ 'title', 'namespaces', 'event-type' ];

	/**
	 * @param WebNotificationQueryStore $queryStore
	 * @param IDatabase $db
	 * @param WebNotificationSchema $schema
	 * @param UserIdentity $forUser
	 */
	public function __construct(
		WebNotificationQueryStore $queryStore, IDatabase $db, WebNotificationSchema $schema, UserIdentity $forUser
	) {
		$this->queryStore = $queryStore;
		$this->forUser = $forUser;
		parent::__construct( $db, $schema );
	}

	/**
	 * @param ReaderParams $params
	 *
	 * @return array
	 */
	public function makeData( $params ) {
		$this->data = [];
		$conds = $this->makePreFilterConds( $params );
		$options = $this->makePreOptionConds( $params );
		// Hardcoded
		$conds['nwqs_target_user'] = $this->forUser->getId();

		$res = $this->queryStore->query( $conds, $options );
		foreach ( $res as $row ) {
			$this->appendRowToData( $row );
		}

		return $this->data;
	}

	/**
	 * @param ReaderParams $params
	 * @return array
	 */
	protected function makePreFilterConds( ReaderParams $params ) {
		$conds = parent::makePreFilterConds( $params );

		foreach ( $params->getFilter() as $filter ) {
			if ( in_array( $filter->getField(), $this->nonSchemaFields ) ) {
				if ( $filter->getComparison() === 'in' ) {
					// This case is not supported by the parent class
					$conds[] =
						$this->fields[$filter->getField()] . ' IN (' . $this->db->makeList( $filter->getValue() ) . ')';
				} else {
					$this->appendPreFilterCond( $conds, $filter );
				}
			}
		}

		return $conds;
	}

	/**
	 * @param array &$conds
	 * @param Filter $filter
	 *
	 * @return void
	 */
	protected function appendPreFilterCond( &$conds, Filter $filter ) {
		if ( !isset( $this->fields[$filter->getField()] ) ) {
			parent::appendPreFilterCond( $conds, $filter );
			return;
		}

		$filterClass = get_class( $filter );
		$newFilter = new $filterClass( [
			'field' => $this->fields[$filter->getField()],
			'comparison' => $filter->getComparison(),
			'value' => $filter->getValue()
		] );
		$filter->setApplied();

		parent::appendPreFilterCond( $conds, $newFilter );
	}

	/**
	 * @param ReaderParams $params
	 *
	 * @return array
	 */
	protected function makePreOptionConds( ReaderParams $params ) {
		$conds = $this->getDefaultOptions();

		$fields = array_values( $this->schema->getSortableFields() );

		foreach ( $params->getSort() as $sort ) {
			if ( !in_array( $sort->getProperty(), $fields ) ) {
				continue;
			}
			if ( !isset( $conds['ORDER BY'] ) ) {
				$conds['ORDER BY'] = "";
			} else {
				$conds['ORDER BY'] .= ",";
			}
			$sortField = $this->fields[$sort->getProperty()];
			$conds['ORDER BY'] .= "$sortField {$sort->getDirection()}";
		}
		return $conds;
	}

	/**
	 * @return string
	 */
	protected function getTableNames() {
		// Not used
		return '';
	}

	/**
	 * @param \stdClass $row
	 *
	 * @return void
	 */
	protected function appendRowToData( \stdClass $row ) {
		$this->data[] = new WebNotificationRecord( (object)[
			WebNotificationRecord::ID => $row->nwqs_notification_id,
			WebNotificationRecord::BUCKETS => explode( '|', $row->nwqs_buckets ),
			WebNotificationRecord::CATEGORIES => explode( '|', $row->nwqs_categories ),
			WebNotificationRecord::NAMESPACE_ID => $row->nwqs_namespace_id,
			WebNotificationRecord::NAMESPACE_TEXT => $row->nwqs_namespace_text,
			WebNotificationRecord::TIMESTAMP => $row->nwqs_notification_timestamp
		] );
	}
}
