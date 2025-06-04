<?php

namespace PageHeader\PageInfoSentence\Type;

use MediaWiki\Html\Html;
use PageHeader\IPageInfo;
use PageHeader\PageInfoSentence\Type;

class Text extends Type {

	/**
	 *
	 * @param IPageInfo $pageInfo
	 * @return string
	 */
	public function build( IPageInfo $pageInfo ): string {
		return Html::element(
			'span',
			array_merge( [
					'class' => $pageInfo->getHtmlClass(),
					'title' => $pageInfo->getTooltipMessage()->text(),
					'id' => $pageInfo->getHtmlId()
				],
				$this->makeHtmlDataArray( $pageInfo->getHtmlDataAttribs() )
			),
			$pageInfo->getLabelMessage()->text()
		);
	}

}
