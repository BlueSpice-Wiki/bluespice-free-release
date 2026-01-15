<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleLink;

class LinkConverter {

	/**
	 *
	 * @param array $linkDescs
	 * @return SimpleLink[]
	 */
	public function getLinkComponents( $linkDescs ) {
		$links = [];
		foreach ( $linkDescs as $linkKey => $linkDesc ) {
			$links[$linkKey] = new SimpleLink( [
				// TODO: Implement
			] );
		}

		return $links;
	}
}
