<?php

namespace BlueSpice\SaferEdit;

use BlueSpice\ExtensionAttributeBasedRegistry;
use Config;
use MediaWiki\Context\IContextSource;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MWStake\MediaWiki\Component\Wire\WireChannelFactory;
use MWStake\MediaWiki\Component\Wire\WireMessage;
use MWStake\MediaWiki\Component\Wire\WireMessenger;
use Wikimedia\Rdbms\ILoadBalancer;

class SaferEditManager {
	/**
	 * @var ILoadBalancer
	 */
	protected $lb;

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var IContextSource
	 */
	protected $context;

	/**
	 * @var ExtensionAttributeBasedRegistry
	 */
	protected $checkerRegistry;

	/**
	 * @var WireMessenger
	 */
	protected $wireMessenger;

	/**
	 * @param ILoadBalancer $lb
	 * @param IContextSource $context
	 * @param Config $config
	 * @param ExtensionAttributeBasedRegistry $checkerRegistry
	 * @param WireMessenger $wireMessenger
	 */
	public function __construct(
		ILoadBalancer $lb, IContextSource $context, Config $config,
		ExtensionAttributeBasedRegistry $checkerRegistry, WireMessenger $wireMessenger
	) {
		$this->context = $context;
		$this->lb = $lb;
		$this->config = $config;
		$this->checkerRegistry = $checkerRegistry;
		$this->wireMessenger = $wireMessenger;
	}

	/**
	 * @param User $user
	 * @param Title $title
	 * @param int $section
	 * @return Status
	 */
	public function saveUserEditing( User $user, Title $title, $section = -1 ) {
		if ( !\MediaWiki\MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan( 'edit', $user, $title )
		) {
			return Status::newFatal( "User cannot edit the page" );
		}

		$table = 'bs_saferedit';
		$fields = [
			"se_timestamp" => wfTimestamp( TS_MW, time() )
		];
		$conditions = [
			"se_user_name" => $user->getName(),
			"se_page_title" => $title->getDBkey(),
			"se_page_namespace" => $title->getNamespace(),
			"se_edit_section" => $section,
		];
		// needed for update reason
		$options = [
			'ORDER BY' => 'se_id DESC',
			'LIMIT' => 1,
		];

		$row = $this->lb->getConnection( DB_REPLICA )->selectRow(
			$table,
			[ 'se_id' ],
			$conditions,
			__METHOD__,
			$options
		);
		if ( $row ) {
			$title->invalidateCache();
			$updateOk = $this->lb->getConnection( DB_PRIMARY )->update(
				$table,
				$fields,
				[ "se_id" => $row->se_id ],
				__METHOD__
			);
			if ( $updateOk ) {
				$this->emitWireMessage( $user, $title );
				return Status::newGood();
			}
		} else {
			$title->invalidateCache();
			$insertOk = $this->lb->getConnection( DB_PRIMARY )->insert(
				$table,
				$conditions + $fields,
				__METHOD__
			);
			if ( $insertOk ) {
				$this->emitWireMessage( $user, $title );
				return Status::newGood();
			}
		}

		return Status::newFatal( "Failed to save editing information" );
	}

	/**
	 * Actually delete all stored saves for a user
	 *
	 * @param User $user User that edited a page
	 * @param Title $title
	 * @return Status
	 */
	public function doClearSaferEdit( User $user, Title $title ) {
		$deleteOk = $this->lb->getConnection( DB_PRIMARY )->delete(
			'bs_saferedit',
			[
				"se_user_name" => $user->getName(),
				"se_page_title" => $title->getDBkey(),
				"se_page_namespace" => $title->getNamespace(),
			],
			__METHOD__
		);

		if ( $deleteOk ) {
			$this->emitWireMessage( $user, $title );
			$title->invalidateCache();
			return Status::newGood();
		}

		return Status::newFatal( "Deletion failed" );
	}

	/**
	 * @param string $func Function to be executed
	 * @param mixed &$result
	 */
	public function askEnvironmentalCheckers( $func, &$result ) {
		foreach ( $this->checkerRegistry->getAllKeys() as $key ) {
			$callable = $this->checkerRegistry->getValue( $key );
			if ( !is_callable( $callable ) ) {
				continue;
			}
			$provider = call_user_func( $callable, $this->context );
			if ( !$provider instanceof IEnvironmentChecker ) {
				continue;
			}
			if ( !$provider->$func( $result ) ) {
				return;
			}
		}
	}

	/**
	 * @param User $user
	 * @param Title $title
	 * @return void
	 */
	private function emitWireMessage( User $user, Title $title ) {
		$warningBuilder = new EditWarningBuilder(
			$this->lb,
			$this->config,
			$user,
			$title
		);

		$wireMessage = new WireMessage(
			( new WireChannelFactory() )->getChannelForPage( $title ),
			[
				'action' => 'bsSaferEditWarning',
				'data' => $warningBuilder->getData()
			]
		);
		$this->wireMessenger->send( $wireMessage );
	}

}
