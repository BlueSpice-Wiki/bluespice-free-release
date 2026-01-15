<?php

namespace BlueSpice\Discovery\Component;

use MediaWiki\Context\IContextSource;
use MediaWiki\Html\TemplateParser;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\Literal;

class AsyncSubpageTree extends Literal {

	public function getId(): string {
		return 'async-subpage-tree';
	}

	public function getHtml(): string {
		$templateParser = new TemplateParser( dirname( __DIR__, 2 ) . '/resources/templates/skeleton' );

		$skeleton = $templateParser->processTemplate(
			'async-tree',
			[]
		);
		return '<div id="subpage-tree" class="subpage-tree"><div id="subpage-tree-skeleton">' .
			$skeleton .
			'</div></div>';
	}

	/**
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public function shouldRender( IContextSource $context ): bool {
		$title = $context->getTitle();

		if ( !$title->isContentPage() ) {
			return false;
		}

		return true;
	}

	public function getRequiredRLModules(): array {
		return [ 'skin.discovery.subpagetree-component' ];
	}
}
