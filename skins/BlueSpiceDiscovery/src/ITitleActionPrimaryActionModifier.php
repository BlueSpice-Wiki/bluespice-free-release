<?php

namespace BlueSpice\Discovery;

interface ITitleActionPrimaryActionModifier {

	/**
	 * @param array $ids
	 * @param string $primaryId
	 * @return string
	 */
	public function getActionId( array $ids, string $primaryId ): string;
}
