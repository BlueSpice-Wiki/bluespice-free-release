<?php

namespace BlueSpice\Discovery\EnhancedSidebar\NodeProcessor;

use BlueSpice\Discovery\EnhancedSidebar\Node\EnhancedSidebarNode;
use BlueSpice\Discovery\EnhancedSidebar\Node\InternalLinkNode;
use Exception;
use MediaWiki\Parser\ParserFactory;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Lib\Nodes\INode;

class InternalLinkProcessor extends EnhancedSidebarNodeProcessor {

	/**
	 * @param ParserFactory $parserFactory
	 * @param TitleFactory $titleFactory
	 * @param PermissionManager $permissionManager
	 */
	public function __construct(
		ParserFactory $parserFactory,
		TitleFactory $titleFactory,
		private readonly PermissionManager $permissionManager,
	) {
		parent::__construct( $parserFactory, $titleFactory );
	}

	/**
	 * @param EnhancedSidebarNode $node
	 *
	 * @return bool
	 */
	public function isHidden( EnhancedSidebarNode $node ): bool {
		$isHidden = parent::isHidden( $node );

		if ( $isHidden || !$this->user ) {
			return $isHidden;
		}

		$title = $this->getTitleFromNode( $node );

		if ( !$title || !$title->exists() ) {
			return $isHidden;
		}

		return !$this->permissionManager->userCan(
			'read',
			$this->user,
			$title
		);
	}

	/**
	 * Serialize in format to be consumed by a tree
	 *
	 * @param EnhancedSidebarNode $node
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function serializeNodeTree( EnhancedSidebarNode $node ): array {
		$title = $this->getTitleFromNode( $node );

		if ( !$title ) {
			return parent::serializeNodeTree( $node );
		}

		return parent::serializeNodeTree( $node ) + [
			'href' => $title->getLocalURL(),
		];
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	public function supportsNodeType( $type ): bool {
		return $type === 'enhanced-sidebar-internal-link';
	}

	/**
	 * @param array $data
	 * @return INode
	 */
	public function getNodeFromData( array $data ): INode {
		return new InternalLinkNode( $data );
	}

	/**
	 * @inheritDoc
	 */
	protected function getKeysToPreprocess( array $data ): array {
		return array_merge( parent::getKeysToPreprocess( $data ), [ 'page' ] );
	}
}
