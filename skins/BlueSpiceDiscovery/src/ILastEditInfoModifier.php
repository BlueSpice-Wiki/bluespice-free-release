<?php

namespace BlueSpice\Discovery;

interface ILastEditInfoModifier {

	/**
	 * @param string $html
	 * @return string
	 */
	public function getHtml( string $html ): string;
}
