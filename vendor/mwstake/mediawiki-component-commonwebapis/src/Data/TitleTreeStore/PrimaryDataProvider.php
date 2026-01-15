<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleTreeStore;

use MediaWiki\Context\RequestContext;
use MediaWiki\Language\Language;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\NamespaceInfo;
use MediaWiki\Title\Title;
use MWStake\MediaWiki\Component\DataStore\ReaderParams;
use MWStake\MediaWiki\Component\DataStore\Schema;
use Wikimedia\Rdbms\IDatabase;

class PrimaryDataProvider extends \MWStake\MediaWiki\Component\CommonWebAPIs\Data\TitleQueryStore\PrimaryDataProvider {
	/** @var string|null */
	private $query = null;
	/** @var array|null */
	private $expandPaths = null;
	/** @var array */
	private $subpageData = [];
	/** @var array */
	private $nodeCache = [];

	/** @var PermissionManager */
	private $permissionManager;

	/**
	 * @inheritDoc
	 */
	public function __construct( IDatabase $db, Schema $schema, Language $language,
		NamespaceInfo $nsInfo, PermissionManager $permissionManager ) {
		parent::__construct( $db, $schema, $language, $nsInfo );
		$this->permissionManager = $permissionManager;
	}

	/**
	 * @param TitleTreeReaderParams $params
	 *
	 * @return \MWStake\MediaWiki\Component\DataStore\Record[]
	 */
	public function makeData( $params ) {
		if ( $params->getExpandPaths() ) {
			$this->expandPaths = $params->getExpandPaths();
		}

		if ( $params->getNode() !== '' ) {
			$node = $params->getNode();
			$node = $this->splitNode( $node );
			if ( $node ) {
				return $this->dataFromNode( $node );
			}
		}

		$this->data = [];
		$res = $this->db->select(
			$this->getTableNames(),
			$this->getFields(),
			$this->makePreFilterConds( $params ),
			__METHOD__,
			$this->makePreOptionConds( $params ),
			$this->getJoinConds( $params )
		);

		$this->subpageData = [];
		foreach ( $res as $row ) {
			$this->subpageData[ $row->page_namespace . '|' . $row->page_title ] = $row;
		}
		foreach ( $res as $row ) {
			$this->appendRowToData( $row );
		}

		return array_values( $this->data );
	}

	/**
	 * @param ReaderParams $params
	 *
	 * @return array
	 */
	protected function makePreFilterConds( ReaderParams $params ) {
		if ( $params->getQuery() !== '' ) {
			$this->query = mb_strtolower( str_replace( '_', ' ', $params->getQuery() ) );
		}
		return parent::makePreFilterConds( $params );
	}

	/**
	 * @param \stdClass $row
	 *
	 * @return void
	 */
	protected function appendRowToData( \stdClass $row ) {
		$indexTitle = $row->mti_title;
		$uniqueId = $this->getUniqueId( $row );

		$user = RequestContext::getMain()->getUser();
		if ( !$this->permissionManager->userCan( 'read', $user, Title::newFromRow( $row ) ) ) {
			return;
		}

		if ( $this->isSubpage( $indexTitle ) &&
			$this->nsInfo->hasSubpages( (int)$row->page_namespace ) ) {
			if (
				$this->queryMatchesSubpage( $indexTitle )
			) {
				$this->insertParents( $row, $uniqueId, true );
			} else {
				if ( !$this->query ) {
					// If page exists, but its parent doesnt, add it
					// This only applies if query is not set
					// We are only checking for root pages, as subpages will be added
					// by getChildren() method
					$bits = explode( '/', $row->page_title );
					$titleToCheck = array_shift( $bits ) . '/dummy';
					$nonExistingRootParent = $this->getParentIfDoesntExist( $row, $titleToCheck );
					if ( $nonExistingRootParent ) {
						$nonExistingNode = $this->getNonExistingRecord( $row, $nonExistingRootParent );
						$nonExistingUniqueId = $this->getUniqueId( $nonExistingNode );
						$nonExistingRecord = $this->makeRecord( $nonExistingNode, $uniqueId, false, false );
						// This is a root node, insert right away
						$this->data[$nonExistingUniqueId] = $nonExistingRecord;
					}
				} else {
					// If query is set, we need to check if any part of the title matches
					// the query. If it does, but that page doesnt exist, we need to add it
					$bits = explode( '/', $row->page_title );
					while ( count( $bits ) > 0 ) {
						$titleToCheck = implode( '/', $bits );
						if ( $this->queryMatchesSubpage( $titleToCheck ) ) {
							if ( !$this->checkPageExists( $row, $titleToCheck ) ) {
								// Insert non-existing page that matches the query, and its parents
								$nonExistingNode = $this->getNonExistingRecord( $row, $titleToCheck );
								$nonExistingUniqueId = $this->getUniqueId( $nonExistingNode );
								$this->insertParents( $nonExistingNode, $nonExistingUniqueId, true );
							}
						}
						array_pop( $bits );
					}
				}

			}
			return;
		}
		if ( $this->isExpandRequested( $row->page_title, (int)$row->page_namespace ) ) {
			$this->expand( $row, $uniqueId, false );
		}

		// Adding root pages
		$this->data[$uniqueId] = $this->makeRecord( $row, $uniqueId, false, false );
	}

	/**
	 * @param \stdClass $row
	 * @param string $uniqueId
	 * @param bool $expanded
	 * @param bool $loaded
	 *
	 * @return TitleTreeRecord
	 */
	private function makeRecord( $row, string $uniqueId, bool $expanded, bool $loaded ) {
		return new TitleTreeRecord( (object)[
			TitleTreeRecord::ID => $uniqueId,
			TitleTreeRecord::PAGE_NAMESPACE => (int)$row->page_namespace,
			TitleTreeRecord::PAGE_TITLE => $row->page_title,
			TitleTreeRecord::PAGE_DBKEY => $row->page_title,
			TitleTreeRecord::IS_CONTENT_PAGE => in_array( $row->page_namespace, $this->contentNamespaces ),
			TitleTreeRecord::PAGE_CONTENT_MODEL => $row->page_content_model,
			TitleTreeRecord::ALLOWS_SUBPAGES => $this->nsInfo->hasSubpages( (int)$row->page_namespace ),
			TitleTreeRecord::LEAF => false,
			TitleTreeRecord::EXPANDED => $expanded,
			TitleTreeRecord::LOADED => $loaded,
			TitleTreeRecord::CHILDREN => property_exists( $row, 'children' ) ? $row->children : []
		] );
	}

	/**
	 * @param \stdClass $row
	 *
	 * @return string
	 */
	private function getUniqueId( $row ): string {
		return (int)$row->page_namespace . ':' . $row->page_title;
	}

	/**
	 * @param string $indexTitle
	 *
	 * @return bool
	 */
	private function isSubpage( string $indexTitle ): bool {
		return strpos( $indexTitle, '/' ) !== false;
	}

	/**
	 * @param string $indexTitle
	 *
	 * @return bool
	 */
	private function queryMatchesSubpage( string $indexTitle ): bool {
		if ( !$this->query ) {
			return false;
		}
		$exploded = explode( '/', $indexTitle );
		// Check only if last part matches, ie.
		// query = 'foo' matches `Bar/foo`, but not `Bar/foo/baz`
		$last = array_pop( $exploded );
		return $this->compareTitleMatchQuery( $last );
	}

	/**
	 * @param \stdClass $row
	 * @param string $uniqueId
	 *
	 * @return void
	 */
	private function expand( \stdClass $row, string $uniqueId ) {
		$row->children = $this->getChildren( $row, null );
		$this->insertParents( $row, $this->getUniqueId( $row ) );
	}

	/**
	 * @param \stdClass $row
	 * @param string $uniqueId
	 * @param bool|null $fromQuery True if row comes from the query, not from traversing the tree
	 *
	 * @return void
	 */
	private function insertParents( \stdClass $row, string $uniqueId, ?bool $fromQuery = false ): void {
		$title = $row->page_title;
		$bits = explode( '/', $title );
		if ( count( $bits ) === 1 ) {
			$this->data[$uniqueId] = $this->makeRecord( $row, $uniqueId, !$fromQuery, !$fromQuery );
			return;
		}
		array_pop( $bits );
		$parentTitle = implode( '/', $bits );
		$parentRow = new \stdClass();
		$parentRow->page_title = $parentTitle;
		$parentRow->page_namespace = $row->page_namespace;
		$parentRow->page_content_model = $row->page_content_model;
		$parentRow->children = $this->getChildren(
			$parentRow,
			$fromQuery
		);
		$this->insertParents( $parentRow, $this->getUniqueId( $parentRow ) );
	}

	/**
	 * @param \stdClass $row
	 * @param bool|null $fromQuery
	 * @param bool $skipRecursive
	 * @return TitleTreeRecord[]
	 */
	private function getChildren( \stdClass $row, $fromQuery = null, $skipRecursive = false ): array {
		$childRows = $this->getSubpages( $row );
		$children = [];
		foreach ( $childRows as $childRow ) {
			$uniqueChildId = $this->getUniqueId( $childRow );

			if ( $fromQuery && !$this->compareTitleMatchQuery( $childRow->page_title ) ) {
				continue;
			}
			if ( !$skipRecursive ) {
				if ( isset( $this->nodeCache[ $uniqueChildId] ) ) {
					$childRow->children = $this->nodeCache[ $uniqueChildId ];
				} else {
					$childRow->children = $this->getChildren(
						$childRow,
						$fromQuery
					);
					$this->nodeCache[ $uniqueChildId ] = $childRow->children;
				}
			} else {
				if ( ( substr_count( $row->page_title, '/' ) + 1 ) !== substr_count( $childRow->page_title, '/' ) ) {
					continue;
				}
			}
			$child = $this->makeRecord( $childRow, $uniqueChildId, false, false );
			$children[] = $child;
		}
		return $children;
	}

	/**
	 *
	 * @param string $title
	 * @return bool
	 */
	private function compareTitleMatchQuery( $title ) {
		$title = mb_strtolower( $title );
		$query = mb_strtolower( $this->query );
		return str_contains( $title, $query );
	}

	/**
	 * @param \stdClass $row
	 *
	 * @return \Wikimedia\Rdbms\IResultWrapper
	 */
	private function getSubpages( \stdClass $row ) {
		$pages = [];
		foreach( $this->subpageData as $subpageRow ) {
			if ( str_starts_with( $subpageRow->page_title, $row->page_title . '/' ) ) {
				$subpage = substr( $subpageRow->page_title, strlen( $row->page_title . '/' ) );
				if ( str_contains( $subpage, '/' ) ) {
					continue;
				}
				if ( $subpageRow->page_namespace !== $row->page_namespace ) {
					continue;
				}
				$pages[] = $subpageRow;
			}
		}

		if ( empty( $this->subpageData ) ) {
			$res = $this->db->select(
				[ 'page' ],
				[ 'page_title', 'page_namespace', 'page_content_model' ],
				[
					'page_namespace' => $row->page_namespace,
					'page_title LIKE ' . $this->db->addQuotes( $row->page_title . '/%' )
				],
				__METHOD__,
				[ 'ORDER BY' => 'page_title' ]
			);

			foreach ( $res as $subpageRow ) {
				$this->subpageData[ $subpageRow->page_namespace . '|' . $subpageRow->page_title ] = $subpageRow;
				$pages[] = $subpageRow;
			}
		}

		$pages = $this->insertNonExistingPages( $pages, $row->page_title );
		usort( $pages, static function ( $a, $b ) {
			return strcmp( $a->page_title, $b->page_title );
		} );

		return $pages;
	}

	/**
	 * @param string $node
	 *
	 * @return array|null
	 */
	private function splitNode( string $node ): ?array {
		$bits = explode( ':', $node );
		if ( count( $bits ) === 1 ) {
			return '0:' . $bits[0];
		}
		$ns = $bits[0];
		$nsIndex = $this->language->getNsIndex( $ns );
		if ( $nsIndex === null ) {
			return null;
		}
		return [
			'page_namespace' => $nsIndex,
			'page_title' => implode( ':', array_slice( $bits, 1 ) )
		];
	}

	/**
	 * @param array $node
	 *
	 * @return array|TitleTreeRecord[]
	 */
	private function dataFromNode( array $node ): array {
		$nodes = $this->getChildren( (object)$node, null, true );
		return $this->expandChildrenNodes( $nodes );
	}

	/**
	 * @param TitleTreeRecord[] $nodes
	 * @return TitleTreeRecord[]
	 */
	private function expandChildrenNodes( $nodes ) {
		if ( $this->expandPaths ) {
			foreach ( $nodes as $node ) {
				foreach ( $this->expandPaths as $path ) {
					$pathParts = $this->splitNode( $path );
					if ( $node->get( TitleTreeRecord::PAGE_TITLE ) === $pathParts['page_title'] &&
						$node->get( TitleTreeRecord::PAGE_NAMESPACE ) === $pathParts['page_namespace'] ) {
							$children = $this->expandChildrenNodes( $this->getChildren( (object)$pathParts, null, true ) );
							$node->set( TitleTreeRecord::CHILDREN, $children );
							$node->set( TitleTreeRecord::EXPANDED, true );
					}
				}
			}
		}
		return $nodes;
	}

	/**
	 * @param string $dbkey
	 * @param int $ns
	 *
	 * @return bool
	 */
	private function isExpandRequested( string $dbkey, int $ns ): bool {
		if ( !$this->expandPaths ) {
			return false;
		}
		foreach ( $this->expandPaths as $path ) {
			$pathParts = $this->splitNode( $path );
			if ( $dbkey === $pathParts['page_title'] && $ns === $pathParts['page_namespace'] ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param \stdClass $row
	 * @param string $title
	 *
	 * @return string|null
	 */
	private function getParentIfDoesntExist( \stdClass $row, string $title ): ?string {
		$bits = explode( '/', $title );
		if ( count( $bits ) === 1 ) {
			// Short-circuit
			return false;
		}
		array_pop( $bits );
		$parent = implode( '/', $bits );
		if ( !$this->checkPageExists( $row, $parent ) ) {
			return $parent;
		}
		return null;
	}

	/**
	 * @param \stdClass $row
	 * @param string $title
	 *
	 * @return bool
	 */
	private function checkPageExists( \stdClass $row, string $title ): bool {
		return isset( $this->subpageData[ $row->page_namespace . '|' . $title ] );
	}

	/**
	 * @param array $res
	 * @param string $parentNode
	 *
	 * @return array
	 */
	private function insertNonExistingPages( array $res, string $parentNode ): array {
		foreach ( $res as $row ) {
			$nonExistingPage = $this->getNonExistingChildOf( $row->page_title, $parentNode, (int)$row->page_namespace );
			if ( $nonExistingPage ) {
				$res[] = (object)[
					'page_title' => $nonExistingPage,
					'page_namespace' => $row->page_namespace,
					'page_content_model' => $row->page_content_model
				];
			}
		}

		return $res;
	}

	/**
	 * @param string $child
	 * @param string $parent
	 * @param int $namespace
	 *
	 * @return mixed|null
	 */
	private function getNonExistingChildOf( string $child, string $parent, int $namespace ): ?string {
		$regex = '/^' . preg_quote( $parent, '/' ) . '+\/[^\/]+(?=\/|$)/';
		$matches = [];
		preg_match( $regex, $child, $matches );
		if ( count( $matches ) === 0 ) {
			return null;
		}
		if ( isset( $this->subpageData[ $namespace . '|' . $matches[0] ] ) ) {
			return null;
		}
		return $matches[0];
	}

	/**
	 * @param \stdClass $row
	 * @param string $nonExistingPageName
	 *
	 * @return \stdClass
	 */
	private function getNonExistingRecord( \stdClass $row, string $nonExistingPageName ) {
		$nonExistingNode = clone $row;
		$nonExistingNode->page_title = $nonExistingPageName;

		return $nonExistingNode;
	}
}
