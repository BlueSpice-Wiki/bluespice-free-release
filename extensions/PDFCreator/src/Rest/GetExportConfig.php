<?php

namespace MediaWiki\Extension\PDFCreator\Rest;

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\PDFCreator\Factory\ModeFactory;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Title\TitleFactory;
use MWStake\MediaWiki\Component\ManifestRegistry\ManifestAttributeBasedRegistry;
use Wikimedia\ParamValidator\ParamValidator;

class GetExportConfig extends SimpleHandler {

	/** @var TitleFactory */
	private $titleFactory;

	/** @var ModeFactory */
	private $modeFactory;

	/**
	 * @param TitleFactory $titleFactory
	 * @param ModeFactory $modeFactory
	 */
	public function __construct( TitleFactory $titleFactory, ModeFactory $modeFactory, ) {
		$this->titleFactory = $titleFactory;
		$this->modeFactory = $modeFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function run() {
		$validated = $this->getValidatedParams();
		$pageId = $validated['pageid'];

		$title = $this->titleFactory->newFromID( $pageId );
		if ( !$title ) {
			return $this->getResponseFactory()->createHttpError( 404, [ 'No valid title to export' ] );
		}
		$modes = $this->modeFactory->getAllProviders();

		$registry = new ManifestAttributeBasedRegistry(
			'PDFCreatorExportPluginModules'
		);
		$modules = [];
		foreach ( $registry->getAllKeys() as $key ) {
			$moduleName = $registry->getValue( $key );
			$modules[] = $moduleName;
		}

		$labels = [];
		$defaultTemplates = [];
		$context = RequestContext::getMain();
		foreach ( $modes as $mode ) {
			if ( !$mode->isRelevantExportMode( $title ) ) {
				continue;
			}
			$modeModules = $mode->getRLModules( $title );
			$modules = array_merge( $modules, $modeModules );
			$labels[ $mode->getKey() ] = $context->msg( $mode->getLabel() )->text();
			$defaultTemplate = $mode->getDefaultTemplate();
			$defaultTemplates[ $mode->getKey() ] = $defaultTemplate;
		}

		$mode = [
			'modules' => array_unique( $modules ),
			'labels' => $labels,
			'defaults' => $defaultTemplates
		];

		return $this->getResponseFactory()->createJson(
			[ 'mode' => $mode ]
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getParamSettings() {
		return [
			'pageid' => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			]
		];
	}
}
