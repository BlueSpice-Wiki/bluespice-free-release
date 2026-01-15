<?php

namespace BlueSpice\Discovery\LangLinksProvider;

use BlueSpice\Discovery\ILangLinksProvider;
use MediaWiki\Title\Title;

class Interwiki implements ILangLinksProvider {

	/**
	 * @param string[] $wikitextLangLinks
	 * @param Title $title
	 * @return string[]
	 */
	public function getLangLinks( array $wikitextLangLinks, Title $title ): array {
		return $wikitextLangLinks;
	}
}
