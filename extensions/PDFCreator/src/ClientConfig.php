<?php

namespace MediaWiki\Extension\PDFCreator;

use MediaWiki\MediaWikiServices;
use MediaWiki\ResourceLoader\Context;

class ClientConfig {

	/**
	 * @return array
	 */
	public static function getTemplateConfig() {
		$util = MediaWikiServices::getInstance()->get( 'PDFCreator.Util' );

		return [
			'templates' => $util->getAvailableTemplateNames()
		];
	}

	/**
	 * @param Context $context
	 * @return array
	 */
	public static function getModeConfig( Context $context ) {
		$modeFactory = MediaWikiServices::getInstance()->get( 'PDFCreator.ExportModeFactory' );

		$modes = $modeFactory->getAllProviders();

		$labels = [];
		foreach ( $modes as $mode ) {
			$labels[ $mode->getKey() ] = $context->msg( $mode->getLabel() )->text();
		}

		return [
			'mode' => $labels
		];
	}

	/**
	 * @param Context $context
	 * @return array
	 */
	public static function getHelpConfig( Context $context ) {
		$pageparamsFactory = MediaWikiServices::getInstance()->get( 'PDFCreator.PageParamsFactory' );

		$pageParamsDesc = $pageparamsFactory->getParamDescription( $context->getLanguage() );
		$pageParams = [];
		foreach ( $pageParamsDesc as $key => $desc ) {
			$pageParams[ $desc->getKey() ] = $context->msg( $desc->getMessage() )->text();
		}
		return [
			'pageParams' => $pageParams
		];
	}
}
