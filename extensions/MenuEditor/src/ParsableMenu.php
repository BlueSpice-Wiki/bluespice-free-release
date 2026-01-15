<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\MenuEditor;

use MediaWiki\Extension\MenuEditor\Parser\IMenuParser;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Title\Title;

interface ParsableMenu extends IMenu {

	/**
	 * @param Title $title
	 * @param RevisionRecord|null $revisionRecord null if no revision exists (new page)
	 *
	 * @return IMenuParser
	 */
	public function getParser( Title $title, ?RevisionRecord $revisionRecord = null ): IMenuParser;
}
