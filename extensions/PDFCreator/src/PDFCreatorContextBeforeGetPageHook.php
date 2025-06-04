<?php

namespace MediaWiki\Extension\PDFCreator;

use MediaWiki\Context\IContextSource;

interface PDFCreatorContextBeforeGetPageHook {

	/**
	 * @param IContextSource $contextSource
	 * @return void
	 */
	public function onPDFCreatorContextBeforeGetPage( IContextSource $contextSource ): void;
}
