<?php

namespace BlueSpice\PageAssignments\Data\Assignable;

use Exception;
use MediaWiki\Context\IContextSource;
use MWStake\MediaWiki\Component\DataStore\IStore;

class Store implements IStore {
	/**
	 *
	 * @var IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @param IContextSource $context
	 * @param \Wikimedia\Rdbms\LoadBalancer $loadBalancer
	 */
	public function __construct( $context, $loadBalancer ) {
		$this->context = $context;
		$this->loadBalancer = $loadBalancer;
	}

	/**
	 *
	 * @return Reader
	 */
	public function getReader() {
		return new Reader( $this->loadBalancer, $this->context );
	}

	/**
	 *
	 * @throws Exception
	 */
	public function getWriter() {
		throw new Exception( 'This store does not support writing!' );
	}

}
