<?php

namespace MediaWiki\Extension\MenuEditor\Api;

use Exception;
use MediaWiki\Extension\MenuEditor\MenuFactory;
use MediaWiki\Extension\MenuEditor\Node\MenuNode;
use MediaWiki\Rest\HttpException;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\RevisionStore;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\Wikitext\ParserFactory;
use MWStake\MediaWiki\Lib\Nodes\INode;
use Wikimedia\ParamValidator\ParamValidator;

class ParseHandler extends MenuHandler {
	/** @var RevisionStore */
	private $revisionStore;

	/**
	 * @param TitleFactory $titleFactory
	 * @param MenuFactory $menuFactory
	 * @param ParserFactory $parserFactory
	 * @param RevisionStore $revisionStore
	 */
	public function __construct(
		TitleFactory $titleFactory, MenuFactory $menuFactory,
		ParserFactory $parserFactory, RevisionStore $revisionStore
	) {
		parent::__construct( $titleFactory, $menuFactory, $parserFactory );
		$this->revisionStore = $revisionStore;
	}

	/**
	 * @return \MediaWiki\Rest\Response|mixed
	 * @throws HttpException
	 */
	public function execute() {
		$params = $this->getValidatedParams();
		$page = $this->makeTitle( $params['pagename'] );
		$revision = $this->getRevision( $page, $params['revid'] );
		if ( !$revision ) {
			return $this->getResponseFactory()->createNoContent();
		}
		$parser = $this->getParserForRevision( $page, $revision );
		$nodes = $parser->parse();
		$data = $params['flat'] ? $nodes : $this->makeTree( $nodes );
		return $this->getResponseFactory()->createJson( $data );
	}

	/**
	 * @return array[]
	 */
	public function getParamSettings() {
		return [
			'pagename' => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_TYPE => 'string',
			],
			'revid' => [
				self::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_TYPE => 'integer',
			],
			'flat' => [
				self::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_TYPE => 'boolean',
				ParamValidator::PARAM_DEFAULT => false
			]
		];
	}

	/**
	 * @param array $nodes
	 * @return array
	 */
	private function makeTree( array $nodes ) {
		$tree = [];
		/** @var MenuNode $node */
		foreach ( $nodes as $node ) {
			if ( $node->getLevel() !== 1 ) {
				continue;
			}
			$tree[] = $this->getNodeData( $node ) + $this->getChildren( $nodes, $node );
		}

		return $tree;
	}

	/**
	 * @param array $nodes
	 * @param MenuNode $node
	 * @return array[]
	 */
	private function getChildren( $nodes, $node ) {
		$parentFound = false;
		$children = [];
		/** @var MenuNode $node */
		foreach ( $nodes as $childNode ) {
			if ( $childNode === $node ) {
				$parentFound = true;
				continue;
			}
			if ( $parentFound ) {
				if ( $childNode->getLevel() === $node->getLevel() + 1 ) {
					$children[] = $this->getNodeData( $childNode ) + $this->getChildren( $nodes, $childNode );
				} elseif ( $childNode->getLevel() <= $node->getLevel() ) {
					return [ 'items' => $children ];
				}
			}
		}

		return [ 'items' => $children ];
	}

	/**
	 * @param INode $node
	 * @return array
	 * @throws Exception
	 */
	private function getNodeData( INode $node ) {
		$data = $node->jsonSerialize();
		unset( $data['wikitext'] );
		$data['name'] = 'menunode_' . random_int( 1, 999999 );

		return $data;
	}

	/**
	 * @param Title $page
	 * @param int|null $revid
	 * @return RevisionRecord|null
	 */
	private function getRevision( Title $page, ?int $revid ): ?RevisionRecord {
		if ( !$page->exists() ) {
			return null;
		}
		if ( $revid === null ) {
			$revid = 0;
		}

		$revision = $this->revisionStore->getRevisionByTitle( $page, $revid );
		if ( !$revision ) {
			return null;
		}

		return $revision;
	}
}
