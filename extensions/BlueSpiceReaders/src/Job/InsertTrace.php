<?php

namespace BlueSpice\Readers\Job;

use BlueSpice\Context;
use BlueSpice\Readers\Data\PageReaders\Store;
use BlueSpice\Readers\Data\Record;
use Job;
use MediaWiki\Context\RequestContext;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MWStake\MediaWiki\Component\DataStore\RecordSet;

class InsertTrace extends Job {

	public const COMMAND = 'BlueSpiceReadersInsertTrace';
	public const PARAM_USER_ID = 'userid';
	public const PARAM_TIMESTAMP = 'ts';
	public const PARAM_REV_ID = 'revId';

	/** @var MediaWikiServices */
	protected $services = null;

	/**
	 *
	 * @param Title $title
	 * @param array $params
	 */
	public function __construct( $title, $params ) {
		parent::__construct( static::COMMAND, $title, $params );
		$this->services = MediaWikiServices::getInstance();
	}

	public function run() {
		$params = $this->getParams();
		$user = $this->services->getUserFactory()->newFromId( $params[ static::PARAM_USER_ID ] );
		if ( !$user || $user->isAnon() ) {
			return;
		}

		if ( empty( $params[static::PARAM_TIMESTAMP] ) ) {
			$params[static::PARAM_TIMESTAMP] = wfTimestampNow();
		}
		$record = new Record( (object)[
			Record::USER_ID => (int)$user->getId(),
			Record::PAGE_ID => (int)$this->getTitle()->getArticleID(),
			Record::TIMESTAMP => $params[static::PARAM_TIMESTAMP],
			Record::USER_NAME => $user->getName(),
			Record::REV_ID => $params[static::PARAM_REV_ID]
		] );

		$this->getStore()->getWriter()->write( new RecordSet( [ $record ] ) );
	}

	/**
	 *
	 * @return Store
	 */
	protected function getStore() {
		return new Store(
			$this->getContext(),
			$this->services->getDBLoadBalancer()
		);
	}

	/**
	 *
	 * @return Context
	 */
	protected function getContext() {
		return new Context(
			RequestContext::getMain(),
			$this->services->getConfigFactory()->makeConfig( 'bsg' )
		);
	}

}
