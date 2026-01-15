<?php

namespace MWStake\MediaWiki\Component\DataStore;

use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;
use Wikimedia\Rdbms\IDatabase;
use Wikimedia\Rdbms\LoadBalancer;

abstract class DatabaseReader extends Reader {

	/**
	 *
	 * @var IDatabase
	 */
	protected $db = null;

	/**
	 *
	 * @param LoadBalancer $loadBalancer
	 * @param IContextSource|null $context
	 * @param Config|null $config
	 */
	public function __construct(
		$loadBalancer, ?IContextSource $context = null, ?Config $config = null
	) {
		parent::__construct( $context, $config );
		$this->db = $loadBalancer->getConnection( DB_REPLICA );
	}
}
