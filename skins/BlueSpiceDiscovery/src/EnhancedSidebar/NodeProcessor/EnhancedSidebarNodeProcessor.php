<?php

namespace BlueSpice\Discovery\EnhancedSidebar\NodeProcessor;

use MediaWiki\Context\RequestContext;
use MediaWiki\Parser\ParserFactory;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MWStake\MediaWiki\Lib\Nodes\INode;
use MWStake\MediaWiki\Lib\Nodes\INodeProcessor;
use MWStake\MediaWiki\Lib\Nodes\INodeSource;

abstract class EnhancedSidebarNodeProcessor implements INodeProcessor {
	/** @var User|null */
	protected $user = null;
	/** @var ParserFactory */
	private $parserFactory;

	/**
	 * @param ParserFactory $parserFactory
	 */
	public function __construct( ParserFactory $parserFactory ) {
		$this->parserFactory = $parserFactory;
	}

	/**
	 * @param User $user
	 *
	 * @return void
	 */
	public function setUser( User $user ) {
		$this->user = $user;
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
			if ( !isset( $data[$key] ) ) {
				continue;
			}
			$data[$key] = $this->parseWikitextValue( $data[$key] );
		}

		return $data;
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
	 * @return INode
	 */
	public function getNode( INodeSource $nodeSource ): INode {
		$data = $this->getProcessedData( $nodeSource );
		$data['hidden'] = $this->isHidden( $data );
		if ( is_string( $data['classes'] ) ) {
			$data['classes'] = [];
		}
		return $this->getNodeFromData( $data );
	}

	/**
	 * Get array if keys that should be preprocessed
	 *
	 * @param array $data Key value pairs
	 *
	 * @return array
	 */
	protected function getKeysToPreprocess( array $data ): array {
		return [ 'text', 'hidden', 'icon-cls' ];
	}

	/**
	 * Use wiki parser to parse value of properties
	 * @param string $text
	 *
	 * @return mixed
	 */
	protected function parseWikitextValue( $text, ?Title $title = null ) {
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
	 * @param array $data
	 *
	 * @return bool
	 */
	protected function isHidden( array $data ): bool {
		if ( !isset( $data['hidden'] ) ) {
			return false;
		}
		if ( is_bool( $data['hidden'] ) ) {
			return $data['hidden'];
		}
		return $data['hidden'] === 'true';
	}
}
