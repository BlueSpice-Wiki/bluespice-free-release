<?php

namespace MWStake\MediaWiki\Component\Wikitext;

use MWStake\MediaWiki\Lib\Nodes\INodeProcessor;

interface IParsoidNodeProcessor extends INodeProcessor {
	/**
	 * List of tags node matches
	 * @return array
	 */
	public function matchTag(): array;

	/**
	 * List of attributes node processor matches
	 * All conditions follow AND rules, only elements matching all
	 * conditions will be considered matches
	 *
	 * Example:
	 * [
	 *    'href' => '/dummy' // Match only elements which have href set to /dummy
	 * 	  'data-dummy' => '*' // Match elements that have data-dummy set (to any value)
	 * ]
	 * @return array
	 */
	public function matchAttributes(): array;

	/**
	 * For more advanced matching logic
	 *
	 * @param \DOMNode $domNode
	 * @param array $attributes DOMNode attributes
	 * @return bool|null true|false if element matches/does not match
	 * and null if no callback logic is set (do not evaluate callback)
	 */
	public function matchCallback( \DOMNode $domNode, $attributes ): ?bool;
}
