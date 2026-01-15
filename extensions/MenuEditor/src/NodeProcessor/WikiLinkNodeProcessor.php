<?php

namespace MediaWiki\Extension\MenuEditor\NodeProcessor;

use MediaWiki\Extension\MenuEditor\Node\WikiLink;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\Wikitext\NodeSource\WikitextSource;
use MWStake\MediaWiki\Lib\Nodes\INode;
use MWStake\MediaWiki\Lib\Nodes\INodeSource;

class WikiLinkNodeProcessor extends MenuNodeProcessor {
	/** @var TitleFactory */
	private $titleFactory;

	/**
	 * @param TitleFactory $titleFactory
	 */
	public function __construct( TitleFactory $titleFactory ) {
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @param string $wikitext
	 * @return bool
	 */
	public function matches( $wikitext ): bool {
		return (bool)preg_match( '/^(\*{1,})\s{0,}\[\[(.*?)\]\]$/', $wikitext );
	}

	/**
	 * @param INodeSource|WikitextSource $source
	 * @return INode
	 */
	public function getNode( INodeSource $source ): INode {
		$link = $this->getNodeValue( $source->getWikitext() );
		$stripped = trim( $link, '[]' );
		$bits = explode( '|', $stripped );
		$target = array_shift( $bits );
		$label = !empty( $bits ) ? array_shift( $bits ) : '';

		return new WikiLink(
			$this->getLevel( $source->getWikitext() ),
			$target, $label,
			$source->getWikitext(), $this->titleFactory
		);
	}

	/**
	 * @inheritDoc
	 */
	public function supportsNodeType( $type ): bool {
		return $type === 'menu-wiki-link';
	}

	/**
	 * @param array $data
	 * @return INode
	 */
	public function getNodeFromData( array $data ): INode {
		return new WikiLink(
			$data['level'],
			$data['target'],
			$data['label'],
			$data['wikitext'] ?? '',
			$this->titleFactory
		);
	}
}
