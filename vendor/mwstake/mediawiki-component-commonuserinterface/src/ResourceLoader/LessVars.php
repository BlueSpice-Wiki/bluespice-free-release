<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\ResourceLoader;

use MediaWiki\Config\Config;
use MediaWiki\Json\FormatJson;
use MediaWiki\MediaWikiServices;
use MediaWiki\Request\WebRequest;
use MediaWiki\ResourceLoader\Context as ResourceLoaderContext;
use MediaWiki\ResourceLoader\FileModule as ResourceLoaderFileModule;
use MWStake\MediaWiki\Component\CommonUserInterface\LessVars as LessVarsObject;

class LessVars extends ResourceLoaderFileModule {

	/**
	 * @param ResourceLoaderContext $context
	 * @return array
	 */
	public function getLessVars( ResourceLoaderContext $context ) {
		$varsFromRequest = $this->getRequestVars( $context->getRequest() );

		$lessVars = LessVarsObject::getInstance();
		return array_merge(
			parent::getLessVars( $context ),
			$lessVars->getAllVars(),
			$varsFromRequest
		);
	}

	/**
	 * @return Config
	 * @since 1.24
	 */
	public function getConfig() {
		return MediaWikiServices::getInstance()->getMainConfig();
	}

	/**
	 * @param WebRequest $request
	 * @return array
	 */
	private function getRequestVars( WebRequest $request ) {
		$value = $request->getVal( 'vars', null );
		if ( !$value ) {
			return [];
		}

		return FormatJson::decode( $value, 1 ) ?? [];
	}
}
