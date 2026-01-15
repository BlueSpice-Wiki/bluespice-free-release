<?php

namespace BlueSpice\Discovery\EnhancedSidebar\NodeProcessor;

use BlueSpice\Discovery\EnhancedSidebar\Node\EnhancedSidebarNode;
use Exception;
use MediaWiki\Context\RequestContext;
use MediaWiki\Parser\ParserFactory;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\User;
use MWStake\MediaWiki\Lib\Nodes\INode;
use MWStake\MediaWiki\Lib\Nodes\INodeProcessor;
use MWStake\MediaWiki\Lib\Nodes\INodeSource;

abstract class EnhancedSidebarNodeProcessor implements INodeProcessor {

	/** @var User|null */
	protected $user = null;

	/**
	 * @param ParserFactory $parserFactory
	 * @param TitleFactory $titleFactory
	 */
	public function __construct(
		private readonly ParserFactory $parserFactory,
		protected readonly TitleFactory $titleFactory
	) {
	}

	/**
	 * @param User $user
	 *
	 * @return void
	 */
	public function setUser( User $user ): void {
		$this->user = $user;
	}

	/**
	 * @param INodeSource $nodeSource
	 *
	 * @return INode
	 */
	public function getRawNode( INodeSource $nodeSource ): INode {
		return $this->getNodeFromData( $nodeSource->getData() );
	}

	/**
	 * @param INodeSource $nodeSource
	 *
	 * @return INode
	 */
	public function getNode( INodeSource $nodeSource ): INode {
		$data = $this->getProcessedData( $nodeSource );

		if ( is_string( $data['classes'] ) ) {
			$data['classes'] = [ $data['classes'] ];
		}

		return $this->getNodeFromData( $data );
	}

	/**
	 * @param EnhancedSidebarNode $node
	 *
	 * @return bool
	 */
	public function isHidden( EnhancedSidebarNode $node ): bool {
		if ( !$node->getReadRestriction() ) {
			return false;
		}

		$parsed = $this->parseWikitextValue( $node->getReadRestriction() );
		if ( !$parsed ) {
			return false;
		}

		if ( is_string( $parsed ) ) {
			$parsed = filter_var( $parsed, FILTER_VALIDATE_BOOLEAN );
		}

		return $parsed;
	}

	/**
	 * Serialize in format to be consumed by a tree
	 *
	 * @param EnhancedSidebarNode $node
	 *
	 * @return array
	 * @throws Exception
	 */
	public function serializeNodeTree( EnhancedSidebarNode $node ): array {
		$data = [
			'id' => $node->generateId(),
			'text' => $node->getDisplayText(),
		];

		$classes = $node->getOutputCssClasses();

		$title = $this->getTitleFromNode( $node );
		if (
			$title && !$title->exists() && !$title->isSpecialPage()
		) {
			$classes = array_merge( $classes, [ 'new' ] );
		}

		if ( !empty( $classes ) ) {
			$data['classes'] = $classes;
		}

		$iconCls = $node->getIconCls();
		if ( $iconCls ) {
			$data['icon-cls'] = $iconCls;
		}

		return $data;
	}

	/**
	 * Use wiki parser to parse value of properties
	 *
	 * @param string $text
	 * @param Title|null $title
	 *
	 * @return mixed
	 */
	protected function parseWikitextValue( $text, ?Title $title = null ): mixed {
		$requestContext = new RequestContext();
		if ( $this->user ) {
			$requestContext->setUser( $this->user );
		}
		if ( $title ) {
			$requestContext->setTitle( $title );
		} else {
			$title = $requestContext->getTitle();
		}

		$parser = $this->parserFactory->create();
		$parser->setPage( $requestContext->getTitle() );
		$parser->setUser( $requestContext->getUser() );
		$parserOptions = ParserOptions::newFromContext( $requestContext );
		$parser->setOptions( $parserOptions );

		return $parser->preprocess( $text, $title, $parserOptions );
	}

	/**
	 * @param INodeSource $nodeSource
	 *
	 * @return array
	 */
	protected function getProcessedData( INodeSource $nodeSource ): array {
		$data = $nodeSource->getData();
		$preprocessable = $this->getKeysToPreprocess( $data );
		unset( $data['type'] );
		// Parse each of the data values
		foreach ( $preprocessable as $key ) {
			if ( !isset( $data[ $key ] ) ) {
				continue;
			}
			$data[ $key ] = $this->parseWikitextValue( $data[ $key ] );
		}

		return $data;
	}

	/**
	 * Get array if keys that should be preprocessed
	 *
	 * @param array $data Key value pairs
	 *
	 * @return array
	 */
	protected function getKeysToPreprocess( array $data ): array {
		return [
			'text',
			'icon-cls'
		];
	}

	/**
	 * @param EnhancedSidebarNode $node
	 *
	 * @return Title|null
	 */
	protected function getTitleFromNode( EnhancedSidebarNode $node ): ?Title {
		$target = $node->getTarget();
		if ( !$target ) {
			return null;
		}

		return $this->titleFactory->newFromDBkey( $target );
	}
}
