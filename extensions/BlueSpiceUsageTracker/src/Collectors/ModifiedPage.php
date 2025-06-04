<?php
namespace BS\UsageTracker\Collectors;

use BS\UsageTracker\CollectorResult;
use MediaWiki\Revision\RevisionStore;
use MediaWiki\Title\Title;

class ModifiedPage extends Base {

	/** @var string */
	protected $namespace = '';

	/** @var string */
	protected $title = '';

	/**
	 * Number of revisions to be considered modified
	 * @var int
	 */
	protected $modifiedRevision = 1;

	/** @var RevisionStore */
	protected $revisionStore;

	/**
	 * @param array $config
	 */
	public function __construct( $config = [] ) {
		parent::__construct( $config );
		$this->namespace = $config['config']['namespace'];
		$this->title = $config['config']['title'];
		if ( isset( $config['config']['modifiedrevision'] ) ) {
			$this->modifiedRevision = $config['config']['modifiedrevision'];
		}
		$this->revisionStore = $this->services->getRevisionStore();
	}

	/**
	 * @inheritDoc
	 */
	public function getUsageData() {
		$res = new CollectorResult( $this );
		$title = Title::makeTitle( $this->namespace, $this->title );
		if ( !$title->exists() ) {
			$res->count = 0;
			return $res;
		}

		$revisionCount = $this->revisionStore->countRevisionsByPageId(
			$this->loadBalancer->getConnection( DB_REPLICA ),
			$title->getArticleID()
		);

		$res->count = ( $revisionCount >= $this->modifiedRevision ) ? 1 : 0;

		return $res;
	}
}
