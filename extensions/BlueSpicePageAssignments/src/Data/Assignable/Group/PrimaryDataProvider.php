<?php

namespace BlueSpice\PageAssignments\Data\Assignable\Group;

use BlueSpice\PageAssignments\IAssignment;
use MediaWiki\Config\GlobalVarConfig;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MWStake\MediaWiki\Component\CommonWebAPIs\Data\GroupStore\GroupRecord;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use MWStake\MediaWiki\Component\Utils\Utility\GroupHelper;

class PrimaryDataProvider extends \MWStake\MediaWiki\Component\CommonWebAPIs\Data\GroupStore\PrimaryDataProvider {
	/** @var Title */
	private $title;

	/**
	 * @param GroupHelper $groupHelper
	 * @param GlobalVarConfig $mwsgConfig
	 * @param Title $title
	 */
	public function __construct( GroupHelper $groupHelper, GlobalVarConfig $mwsgConfig, Title $title ) {
		parent::__construct( $groupHelper, $mwsgConfig );
		$this->title = $title;
	}

	/**
	 * @param ReaderParams $params
	 *
	 * @return array
	 */
	public function makeData( $params ) {
		$parentData = parent::makeData( $params );

		$this->data = [];
		foreach ( $parentData as $dataItem ) {
			$this->appendRowToData( $dataItem->get( GroupRecord::GROUP_NAME ) );
		}
		return $this->data;
	}

	/**
	 * @return string[]
	 */
	protected function getGroupFilter(): array {
		return [ 'core-minimal', 'extension-minimal', 'custom' ];
	}

	/**
	 *
	 * @param string $groupname
	 */
	protected function appendRowToData( $groupname ) {
		$assignmentFactory = MediaWikiServices::getInstance()->getService(
			'BSPageAssignmentsAssignmentFactory'
		);

		$assignment = $assignmentFactory->factory(
			'group',
			$groupname,
			$this->title
		);
		if ( !$assignment instanceof IAssignment ) {
			// :(
			return;
		}

		$this->data[] = $assignment->getRecord();
	}
}
