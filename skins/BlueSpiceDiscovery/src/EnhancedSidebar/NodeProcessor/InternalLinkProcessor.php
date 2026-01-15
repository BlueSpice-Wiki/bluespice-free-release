<?php

namespace BlueSpice\Discovery\EnhancedSidebar\NodeProcessor;

use BlueSpice\Discovery\EnhancedSidebar\Node\InternalLinkNode;
use MediaWiki\Parser\ParserFactory;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Lib\Nodes\INode;
use MWStake\MediaWiki\Lib\Nodes\INodeSource;

class InternalLinkProcessor extends EnhancedSidebarNodeProcessor {

	/** @var TitleFactory */
	private $titleFactory;

	/** @var PermissionManager */
	private $permissionManager;

	/**
	 * @param PermissionManager $permissionManager
	 * @param ParserFactory $parserFactory
	 * @param TitleFactory $titleFactory
	 */
	public function __construct(
		PermissionManager $permissionManager, ParserFactory $parserFactory,
		TitleFactory $titleFactory
	) {
		parent::__construct( $parserFactory );
		$this->titleFactory = $titleFactory;
		$this->permissionManager = $permissionManager;
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	public function supportsNodeType( $type ): bool {
		return $type === 'enhanced-sidebar-internal-link';
	}

	/**
	 * @inheritDoc
	 */
	protected function getProcessedData( INodeSource $nodeSource ): array {
		$data = parent::getProcessedData( $nodeSource );
		$data['page'] = $this->getTitleFromParam( $data['page'] );
		return $data;
	}

	/**
	 * @param array $data
	 * @return INode
	 */
	public function getNodeFromData( array $data ): INode {
		return new InternalLinkNode( $this->getPermissionManager(), $data );
	}

	/**
	 * @param string $pagename
	 * @return Title
	 */
	protected function getTitleFromParam( $pagename ): Title {
		return $this->titleFactory->newFromText( $pagename ) ?? $this->titleFactory->newMainPage();
	}

	/**
	 * @inheritDoc
	 */
	protected function getKeysToPreprocess( array $data ): array {
		return array_merge( parent::getKeysToPreprocess( $data ), [ 'page' ] );
	}

	/**
	 * @return PermissionManager
	 */
	protected function getPermissionManager(): PermissionManager {
		return $this->permissionManager;
	}
}
