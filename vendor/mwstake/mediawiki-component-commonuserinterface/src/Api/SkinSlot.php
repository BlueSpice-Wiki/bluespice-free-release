<?php

namespace MWStake\MediaWiki\Component\CommonUserInterface\Api;

use MediaWiki\Api\ApiBase;
use MediaWiki\Context\DerivativeContext;
use MediaWiki\Context\IContextSource;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\CommonUserInterface\ComponentManager;
use MWStake\MediaWiki\Component\CommonUserInterface\RendererDataTreeBuilder;
use Wikimedia\ParamValidator\ParamValidator;

class SkinSlot extends ApiBase {
	public function execute() {
		$path = $this->getParameter( 'path' );
		$pathParts = explode( '/', $path );
		$slotId = array_shift( $pathParts );
		$context = $this->makeContext();

		$services = MediaWikiServices::getInstance();

		/** @var ComponentManager */
		$componentManager = $services->getService( 'MWStakeCommonUIComponentManager' );
		$componentManager->init( $context, true, $path );

		/** @var RendererDataTreeBuilder */
		$rendererDataTreeBuilder = $services->getService( 'MWStakeCommonUIRendererDataTreeBuilder' );

		$componentTree = $componentManager->getSkinSlotComponentTree( $slotId );
		$rendererDataTree = $rendererDataTreeBuilder->getRendererDataTree( $componentTree );

		$result = $this->getResult();
		$result->addValue( null, 'data', $rendererDataTree );
		$result->addValue( null, 'rlModules', $componentManager->getRLModules() );
		$result->addValue( null, 'rlModuleStyles', $componentManager->getRLModuleStyles() );
	}

	/**
	 * Called by ApiMain
	 * @return array
	 */
	public function getAllowedParams() {
		return parent::getAllowedParams() + [
			'path' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_DEFAULT => '',
				static::PARAM_HELP_MSG => 'apihelp-mwstake-commonui-skinslot-param-path',
			],
			'context' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
				ParamValidator::PARAM_DEFAULT => '{}',
				static::PARAM_HELP_MSG => 'apihelp-mwstake-commonui-skinslot-param-context',
			],
		];
	}

	/**
	 *
	 * @return IContextSource
	 */
	private function makeContext() {
		$context = new DerivativeContext( $this->getContext() );
		return $context;
	}
}
