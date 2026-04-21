<?php

namespace BlueSpice\Authors;

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Title\Title;
use Wikimedia\Rdbms\SelectQueryBuilder;

class AuthorsList {

	/**
	 *
	 * @var \Wikimedia\Rdbms\LoadBalancer
	 */
	protected $loadBalancer = null;

	/**
	 *
	 * @var Title
	 */
	protected $title = null;

	/**
	 *
	 * @var string[]
	 */
	protected $blacklist = [];

	/**
	 *
	 * @var int
	 */
	protected $limit = 1;

	/**
	 *
	 * @var bool
	 */
	protected $more = false;

	/** @var MediaWikiServices */
	protected $services = null;

	/**
	 *
	 * @param Title $title
	 * @param array $blacklist
	 * @param int $limit
	 * @param \Wikimedia\Rdbms\LoadBalancer|null $loadBalancer
	 */
	public function __construct( $title, $blacklist, $limit = 0, $loadBalancer = null ) {
		$this->title = $title;
		$this->blacklist = $blacklist;
		$this->loadBalancer = $loadBalancer;
		$this->limit = $limit;
		$this->services = MediaWikiServices::getInstance();

		if ( $this->loadBalancer === null ) {
			$this->loadBalancer = $this->services->getDBLoadBalancer();
		}
	}

	/**
	 * Find first editor. If editor is on blacklist return empty string.
	 * @param RevisionRecord $revision
	 * @return string The originators username
	 *
	 */
	public function getOriginator( $revision ) {
		if ( $revision instanceof RevisionRecord === false ) {
			return '';
		}

		if ( !$revision->getUser() ) {
			return '';
		}

		$originator = $revision->getUser()->getName();

		if ( $this->services->getUserNameUtils()->isIP( $originator ) ) {
			return '';
		}
		if ( in_array( $originator, $this->blacklist ) ) {
			return '';
		}
		return $originator;
	}

	/**
	 * @return string[]
	 */
	public function getEditors() {
		$usertexts = $this->loadAllUserTexts();

		if ( empty( $usertexts ) ) {
			return [];
		}

		$count = count( $usertexts );
		$items = 0;
		$editors = [];
		$userNameUtils = $this->services->getUserNameUtils();
		$userFactory = $this->services->getUserFactory();

		while ( $items < $count ) {
			if ( $this->limit && $items > ( $this->limit - 1 ) ) {
				$this->more = true;
				break;
			}

			if ( $userNameUtils->isIP( $usertexts[$items] ) ) {
				unset( $usertexts[$items] );
				$items++;
				continue;
			}

			$user = $userFactory->newFromName( $usertexts[$items] );

			if ( !is_object( $user ) || in_array( $user->getName(), $this->blacklist ) ) {
				unset( $usertexts[$items] );
				$items++;
				continue;
			}

			$editors[] = $usertexts[$items];
			$items++;
		}

		return $editors;
	}

	/**
	 *
	 * @return bool
	 */
	public function moreEditors() {
		return $this->more;
	}

	/**
	 * @return string[]
	 */
	protected function loadAllUserTexts() {
		$dbr = $this->services->getConnectionProvider()->getReplicaDatabase();
		$res = $this->services->getRevisionStore()
			->newSelectQueryBuilder( $dbr )
			->where( [ 'rev_page' => $this->title->getArticleID() ]	)
			->orderBy( 'rev_timestamp', SelectQueryBuilder::SORT_DESC )
			->caller( __METHOD__ )
			->fetchResultSet();

		$authors = [];
		foreach ( $res as $row ) {
			$authors[] = $row->rev_user_text;
		}

		return array_values( array_unique( $authors ) );
	}
}
