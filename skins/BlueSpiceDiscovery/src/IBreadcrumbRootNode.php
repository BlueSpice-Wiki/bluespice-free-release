<?php

namespace BlueSpice\Discovery;

use MediaWiki\Title\Title;

interface IBreadcrumbRootNode {

	/**
	 *
	 * @return Title $title
	 */
	public function getLinkTarget(): Title;

}
