<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface;

interface ISkinSlotRenderer {

	/**
	 * @param array $data
	 * @return string
	 */
	public function getHtml( $data = [] ): string;
}
