<?php

namespace MediaWiki\Extension\PDFCreator;

interface IShowNamespaceAware {

	/**
	 * @param bool $showNamespace
	 * @return void
	 */
	public function setShowNamespace( bool $showNamespace ): void;
}
