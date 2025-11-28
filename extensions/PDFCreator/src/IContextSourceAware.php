<?php

namespace MediaWiki\Extension\PDFCreator;

use MediaWiki\Context\IContextSource;

interface IContextSourceAware {

	public function setContext( IContextSource $context ): void;
}
