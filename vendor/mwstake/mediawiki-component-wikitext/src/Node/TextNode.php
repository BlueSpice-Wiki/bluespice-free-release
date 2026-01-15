<?php

namespace MWStake\MediaWiki\Component\Wikitext\Node;

use MWStake\MediaWiki\Lib\Nodes\MutableNode;

class TextNode extends MutableNode {
	/**
	 * @return string
	 */
	public function getType(): string {
		return 'text';
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'text',
			'wikitext' => $this->getOriginalData()
		];
	}
}
