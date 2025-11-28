<?php

namespace MediaWiki\Extension\PDFCreator;

interface IWorkspaceAware {

	/**
	 * @param string $workspace
	 *
	 * @return void
	 */
	public function setWorkspace( string $workspace ): void;
}
