<?php

namespace BlueSpice\WhoIsOnline\Tag;

use BlueSpice\Tag\Handler;
use BlueSpice\WhoIsOnline\Data\Record;
use BlueSpice\WhoIsOnline\Tracer;
use MediaWiki\Config\Config;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;

class PopUpHandler extends Handler {

	/**
	 * @var int
	 */
	protected static $idCounter = 0;

	/**
	 * @var Tracer
	 */
	protected $tracer = null;

	/**
	 * @param string $processedInput
	 * @param array $processedArgs
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @param Tracer|null $tracer
	 */
	public function __construct(
		$processedInput, array $processedArgs, Parser $parser, PPFrame $frame, ?Tracer $tracer = null
	) {
		parent::__construct( $processedInput, $processedArgs, $parser, $frame );
		if ( !$tracer ) {
			$tracer = MediaWikiServices::getInstance()->getService( 'BSWhoIsOnlineTracer' );
		}
		$this->tracer = $tracer;
	}

	/**
	 * @return string
	 */
	public function handle() {
		$recordSet = $this->tracer->getTracedRecords();

		$this->parser->getOutput()->setPageProperty( 'bs-tag-userscount', 1 );
		$targetId = $this->getTargetId();

		$users = [];
		foreach ( $recordSet->getRecords() as $record ) {
			$users[] = $record->get( Record::USER_NAME );
		}

		$anchor = Html::element( 'a', [
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
			( empty( $this->processedArgs[PopUp::PARAM_ANCHOR_TEXT] )
				? wfMessage( 'bs-whoisonline-widget-title' )
				: $this->processedArgs[PopUp::PARAM_ANCHOR_TEXT] )
		);

		return $anchor;
	}

	/**
	 * @return string
	 */
	protected function getTargetId() {
		return 'bs-wo-link-' . static::$idCounter++;
	}

	/**
	 * @return Config
	 */
	protected function getConfig() {
		return MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );
	}
}
