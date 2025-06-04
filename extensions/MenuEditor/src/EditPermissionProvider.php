<?php

namespace MediaWiki\Extension\MenuEditor;

interface EditPermissionProvider {
	/**
	 * @return string
	 */
	public function getEditRight(): string;
}
