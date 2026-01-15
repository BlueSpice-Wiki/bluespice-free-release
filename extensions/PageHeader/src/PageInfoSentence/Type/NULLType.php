<?php

namespace PageHeader\PageInfoSentence\Type;

use PageHeader\IPageInfo;
use PageHeader\PageInfoSentence\Type;

class NULLType extends Type {

	/**
	 *
	 * @param IPageInfo $pageInfo
	 * @return string
	 */
	public function build( IPageInfo $pageInfo ): string {
		return '';
	}

}
