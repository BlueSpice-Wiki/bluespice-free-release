<?php

namespace BlueSpice\InstanceStatus\InstanceStatusProvider;

use BlueSpice\InstanceStatus\IStatusProvider;
use MediaWiki\Context\RequestContext;
use MediaWiki\Language\Language;
use MediaWiki\Message\Message;
use Wikimedia\Rdbms\ILoadBalancer;

class Created implements IStatusProvider {
	/** @var ILoadBalancer */
	 private $lb;
	 /** @var Language */
	 private $language;

	/**
	 * @param ILoadBalancer $lb
	 * @param Language $language
	 */
	public function __construct( ILoadBalancer $lb, Language $language ) {
		$this->lb = $lb;
		$this->language = $language;
	}

	/**
	 * @return string
	 */
	public function getLabel(): string {
		return Message::newFromKey( 'bs-instancestatus-instance-status-created-label' )->text();
	}

	/**
	 * @return string
	 */
	public function getValue(): string {
		$data = $this->getConfigVar();
		if ( !$data ) {
			return Message::newFromKey(
				'bs-instancestatus-instance-status-no-info'
			)->text();
		}
		return $this->language->userDate( $data, RequestContext::getMain()->getUser() );
	}

	/**
	 * @return mixed|string
	 */
	protected function getConfigVar() {
		$dbr = $this->lb->getConnection( DB_REPLICA );
		$res = $dbr->newSelectQueryBuilder()
			->select( [ 'min(rev_timestamp)' ] )
			->from( 'revision' )
			->caller( __METHOD__ )
			->fetchResultSet();
		$arr = [];
		foreach ( $res as $row ) {
			array_push( $arr, $row );
		}
		$instanceCreated = array_column( $arr, 'min(rev_timestamp)' )[0];
		return ( !$instanceCreated ) ? '' : $instanceCreated;
	}

	/**
	 * @return string
	 */
	public function getIcon(): string {
		return 'lightbulb';
	}

	/**
	 * @return int
	 */
	public function getPriority(): int {
		return 40;
	}
}
