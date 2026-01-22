<?php

namespace BlueSpice\WhoIsOnline\Tag;

use BlueSpice\WhoIsOnline\Data\Record;
use BlueSpice\WhoIsOnline\Tracer;
use MediaWiki\Html\Html;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;

class PopupHandler implements ITagHandler {

	/**
	 * @param Tracer $tracer
	 * @param int $id
	 */
	public function __construct(
		private readonly Tracer $tracer,
		private readonly int $id
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function getRenderedContent( string $input, array $params, Parser $parser, PPFrame $frame ): string {
		$recordSet = $this->tracer->getTracedRecords();

		$parser->getOutput()->setPageProperty( 'bs-tag-userscount', 1 );
		$targetId = $this->getTargetId();

		$users = [];
		foreach ( $recordSet->getRecords() as $record ) {
			$users[] = $record->get( Record::USER_NAME );
		}

		return Html::element( 'a', [
			'class' => 'wo-link',
			'title' => wfMessage( 'bs-whoisonline-widget-title' ),
			'data-target-id' => $targetId,
			'data-target' => empty( $users ) ? '' : implode( ',', $users ),
			'tabindex' => '0',
			'aria-haspopup' => 'true',
			'aria-expanded' => 'false',
			'aria-controls' => $targetId,
			'role' => 'button'
		],
			( empty( $params['anchortext'] )
				? wfMessage( 'bs-whoisonline-widget-title' )
				: $params['anchortext'] )
		);
	}

	/**
	 * @return string
	 */
	protected function getTargetId() {
		return 'bs-wo-link-' . $this->id;
	}
}
