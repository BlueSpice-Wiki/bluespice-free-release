<?php

namespace MWStake\MediaWiki\Component\Wikitext\Parser;

use MediaWiki\Content\Content;
use MediaWiki\Content\WikitextContent;
use MWStake\MediaWiki\Lib\Nodes\IMutableNode;
use MWStake\MediaWiki\Lib\Nodes\INode;
use MWStake\MediaWiki\Lib\Nodes\MutableParser;

abstract class MutableWikitextParser extends MutableParser {
	/**
	 * @inheritDoc
	 */
	public function addNode( INode $node, $mode = 'append', $newline = true ): void {
		$newText = $node instanceof IMutableNode ?
			$node->getCurrentData() : $node->getOriginalData();
		switch ( $mode ) {
			case 'prepend':
				if ( $newline ) {
					$newText .= "\n";
				}
				$this->rawData = $newText . $this->rawData;
				break;
			case 'append':
			default:
				if ( $newline ) {
					$this->rawData .= "\n";
				}
				$this->rawData .= $newText;
				break;
		}
		$this->setRevisionContent();
	}

	/**
	 * @param IMutableNode $node
	 * @return bool
	 */
	public function replaceNode( IMutableNode $node ): bool {
		if ( $node->getOriginalData() === $node->getCurrentData() ) {
			return true;
		}
		if ( !$this->nodeExistsInText( $node ) ) {
			return false;
		}
		$this->rawData = str_replace(
			$node->getOriginalData(), $node->getCurrentData(), $this->rawData
		);
		$this->setRevisionContent();

		return true;
	}

	/**
	 * @param INode $node
	 * @return bool
	 */
	public function removeNode( INode $node ): bool {
		if ( !$this->nodeExistsInText( $node ) ) {
			return false;
		}

		$nodeText = preg_quote( $node->getOriginalData() );
		$this->rawData = preg_replace(
			"/\n{$nodeText}|{$nodeText}|{$nodeText}\n/",
			'', $this->rawData
		);
		$this->setRevisionContent();

		return true;
	}

	/**
	 * @param INode $node
	 * @return false|int
	 */
	private function nodeExistsInText( INode $node ): bool {
		$toTest = preg_quote( $node->getOriginalData() );
		return (bool)preg_match( '/' . $toTest . '/', $this->rawData );
	}

	/**
	 * @inheritDoc
	 */
	protected function getContentObject(): Content {
		return new WikitextContent( $this->rawData );
	}
}
