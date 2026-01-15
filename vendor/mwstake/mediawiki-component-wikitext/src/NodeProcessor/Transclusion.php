<?php

namespace MWStake\MediaWiki\Component\Wikitext\NodeProcessor;

use MWStake\MediaWiki\Component\Wikitext\IParsoidNodeProcessor;
use MWStake\MediaWiki\Component\Wikitext\NodeSource\ParsoidSource;
use MWStake\MediaWiki\Lib\Nodes\INode;
use MWStake\MediaWiki\Lib\Nodes\INodeSource;

class Transclusion implements IParsoidNodeProcessor {
	/**
	 * @inheritDoc
	 */
	public function matchTag(): array {
		return [ 'span', 'p', 'div' ];
	}

	/**
	 * @inheritDoc
	 */
	public function supportsNodeType( $type ): bool {
		return $type === 'transclusion';
	}

	/**
	 * @inheritDoc
	 */
	public function matchAttributes(): array {
		return [
			'typeof' => 'mw:Transclusion',
			'data-mw' => '*'
		];
	}

	/**
	 * @inheritDoc
	 */
	public function matchCallback( \DOMNode $domNode, $attributes ): ?bool {
		return null;
	}

	/**
	 * @param INodeSource|ParsoidSource $source
	 * @return INode
	 */
	public function getNode( INodeSource $source ): INode {
		$domNode = $source->getDOMNode();
		$attributes = $source->getAttributes();

		$data = json_decode( $attributes['data-mw'], 1 );
		$template = null;
		foreach ( $data['parts'] as $part ) {
			if ( isset( $part['template'] ) ) {
				$template = $part['template'];
				break;
			}
		}
		if ( !$template ) {
			throw new \UnexpectedValueException( 'No template data found' );
		}
		return new \MWStake\MediaWiki\Component\Wikitext\Node\Transclusion(
			trim( $template['target']['wt'] ),
			$this->parseParams( $template['params'] ),
			$source->getWikitext()
		);
	}

	/**
	 * @param array $data
	 * @return INode
	 */
	public function getNodeFromData( array $data ): INode {
		throw new \BadMethodCallException( 'Not implemented' );
	}

	/**
	 * @param array $params
	 * @return mixed
	 */
	private function parseParams( $params ) {
		array_walk( $params, static function ( &$value, $key ) {
			$value = isset( $value['wt'] ) ? $value['wt'] : $value;
		} );
		return $params;
	}
}
