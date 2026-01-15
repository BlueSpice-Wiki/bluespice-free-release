<?php

namespace PageHeader\PageInfoSentence;

use PageHeader\IPageInfo;

interface IType {

	/**
	 *
	 * @param IPageInfo $pageInfo
	 * @return string
	 */
	public function build( IPageInfo $pageInfo ): string;
}
