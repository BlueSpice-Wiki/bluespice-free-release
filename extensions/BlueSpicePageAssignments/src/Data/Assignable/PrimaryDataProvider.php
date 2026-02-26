<?php

namespace BlueSpice\PageAssignments\Data\Assignable;

use BlueSpice\PageAssignments\Data\Record;
use LogicException;
use MediaWiki\Context\IContextSource;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\DataStore\IPrimaryDataProvider;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use RuntimeException;

class PrimaryDataProvider implements IPrimaryDataProvider {

	/**
	 *
	 * @var \MWStake\MediaWiki\Component\DataStore\Record[]
	 */
	protected $data = [];

	/**
	 *
	 * @var \Wikimedia\Rdbms\IDatabase
	 */
	protected $db = null;

	/**
	 *
	 * @var IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @param \Wikimedia\Rdbms\IDatabase $db
	 * @param IContextSource $context
	 */
	public function __construct( $db, $context ) {
		$this->db = $db;
		$this->context = $context;
	}

	/**
	 * @param ReaderParams $params
	 * @return array
	 * @throws LogicException
	 * @throws RuntimeException
	 */
	public function makeData( $params ) {
		$this->data = [];

		$title = $this->context->getTitle();
		if ( !$title ) {
			throw new LogicException( "Missing assignable title" );
		}
		if ( $title->getArticleID() < 1 ) {
			throw new RuntimeException(
				"Not an assignable title: '{$title->getFullText()}'"
			);
		}
		$assignableFactory = MediaWikiServices::getInstance()->getService(
			'BSPageAssignmentsAssignableFactory'
		);

		$activatedTypes = $this->context->getConfig()->get(
			'PageAssignmentsActivatedTypes'
		);

		foreach ( $assignableFactory->getRegisteredTypes() as $type ) {
			if ( !in_array( $type, $activatedTypes ) ) {
				continue;
			}
			$assignable = $assignableFactory->factory(
				$type,
				$this->context
			);
			$recordSet = $assignable->getStore()->getReader()->read( $params );
			foreach ( $recordSet->getRecords() as $record ) {
				$this->appendRowToData( $record );
			}
		}

		return $this->data;
	}

	/**
	 *
	 * @param Record $record
	 */
	protected function appendRowToData( Record $record ) {
		$this->data[] = $record;
	}
}
