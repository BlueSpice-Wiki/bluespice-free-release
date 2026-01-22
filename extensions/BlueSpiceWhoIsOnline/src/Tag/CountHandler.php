<?php

namespace BlueSpice\WhoIsOnline\Tag;

use BlueSpice\WhoIsOnline\Tracer;
use MediaWiki\Html\Html;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;

class CountHandler implements ITagHandler {

	/**
	 * @param Tracer $tracer
	 */
	public function __construct(
		private readonly Tracer $tracer
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function getRenderedContent( string $input, array $params, Parser $parser, PPFrame $frame ): string {
		$recordSet = $this->tracer->getTracedRecords();

		$parser->getOutput()->setPageProperty( 'bs-tag-userscount', 1 );
		return Html::element(
			'span',
			[ 'class' => 'bs-whoisonline-count' ],
			$recordSet->getTotal()
		);
	}
}
