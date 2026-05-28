<?php

namespace MWStake\MediaWiki\Component\DataStore;

use InvalidArgumentException;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\User;

class PermissionTrimmer extends LimitContinueTrimmer {

	/**
	 * Total number of records possible from this result set
	 *
	 * @var int
	 */
	private int $total = 0;

	/**
	 *
	 * @param TitleFactory $titleFactory
	 * @param PermissionManager $permissionManager
	 * @param User $user
	 * @param int $limit
	 * @param array $continueFrom
	 * @param int $offset
	 */
	public function __construct(
		private readonly TitleFactory $titleFactory,
		private readonly PermissionManager $permissionManager,
		private readonly User $user,
		int $limit = ReaderParams::LIMIT_INFINITE,
		array $continueFrom = [],
		int $offset = 0
	) {
		parent::__construct( $limit, $continueFrom, $offset );
	}

	/**
	 *
	 * @param \MWStake\MediaWiki\Component\DataStore\Record[] $dataSets
	 * @return \MWStake\MediaWiki\Component\DataStore\Record[]
	 */
	public function trim( $dataSets ) {
		$this->total = count( $dataSets );
		$set = $this->getRelevantSet( $dataSets );
		$this->nextContinue = [];

		$trimmed = [];
		$setFull = false;
		foreach ( $set as  $record ) {
			if ( !( $record instanceof IContinueAwareRecord ) ) {
				throw new InvalidArgumentException(
					'PermissionTrimmer can be used only with records implementing ITitleAwareRecord'
				);
			}
			$title = $record->getTitle( $this->titleFactory );
			if ( !$title ) {
				$this->total--;
				continue;
			}
			if ( !$this->permissionManager->quickUserCan( 'read', $this->user, $title ) ) {
				$this->total--;
				continue;
			}
			if ( $setFull ) {
				// Set continue to next readable record
				$this->nextContinue = $record->getContinueValue();
				break;
			}
			$trimmed[] = $record;
			if ( count( $trimmed ) === $this->getLimit() ) {
				$setFull = true;
			}
		}
		return array_values( $trimmed );
	}

	/**
	 * @return int
	 */
	public function getTotal(): int {
		return $this->total;
	}
}
