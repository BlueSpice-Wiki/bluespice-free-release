<?php

namespace BlueSpice\VisualEditorConnector\Hook\Messages;

use MediaWiki\Cache\Hook\MessagesPreLoadHook;
use MediaWiki\Config\ConfigFactory;

class PreLoadMessages implements MessagesPreLoadHook {

	/** @var ConfigFactory */
	private $configFactory = null;

	/**
	 * @param ConfigFactory $configFactory
	 */
	public function __construct( ConfigFactory $configFactory ) {
		$this->configFactory = $configFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function onMessagesPreLoad( $title, &$message, $code ) {
		if ( !str_contains( $title, 'Visualeditor-help-link' ) ) {
			return;
		}

		$bsgConfig = $this->configFactory->makeConfig( 'bsg' );
		$helpUrl = $bsgConfig->get( 'VisualEditorConnectorHelpUrl' );
		$message = $helpUrl;
	}

}
