<?php

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\PDFCreator\Factory\ExportBackendFactory;
use MediaWiki\Extension\PDFCreator\Factory\ExportModuleFactory;
use MediaWiki\Extension\PDFCreator\Factory\ExportPageFactory;
use MediaWiki\Extension\PDFCreator\Factory\ExportPostProcessorFactory;
use MediaWiki\Extension\PDFCreator\Factory\ExportPreProcessorFactory;
use MediaWiki\Extension\PDFCreator\Factory\ExportProcessorFactory;
use MediaWiki\Extension\PDFCreator\Factory\ExportSpecificationFactory;
use MediaWiki\Extension\PDFCreator\Factory\ExportTargetFactory;
use MediaWiki\Extension\PDFCreator\Factory\HtmlProviderFactory;
use MediaWiki\Extension\PDFCreator\Factory\MetaDataFactory;
use MediaWiki\Extension\PDFCreator\Factory\ModeFactory;
use MediaWiki\Extension\PDFCreator\Factory\PageParamsFactory;
use MediaWiki\Extension\PDFCreator\Factory\PageSpecFactory;
use MediaWiki\Extension\PDFCreator\Factory\StyleBlocksFactory;
use MediaWiki\Extension\PDFCreator\Factory\StylesheetsFactory;
use MediaWiki\Extension\PDFCreator\Factory\TemplateProviderFactory;
use MediaWiki\Extension\PDFCreator\PDFCreator;
use MediaWiki\Extension\PDFCreator\PDFCreatorUtil;
use MediaWiki\Extension\PDFCreator\Utility\ExportHtmlBuilder;
use MediaWiki\Extension\PDFCreator\Utility\MediaWikiCommonCssProvider;
use MediaWiki\Extension\PDFCreator\Utility\MustacheTemplateParser;
use MediaWiki\Extension\PDFCreator\Utility\TemplateValueExtractor;
use MediaWiki\Extension\PDFCreator\Utility\TemplateValueInsertor;
use MediaWiki\Extension\PDFCreator\Utility\WikiTemplateParser;
use MediaWiki\MediaWikiServices;

return [
	'PDFCreator.ExportSpecificationFactory' => static function (
		MediaWikiServices $services ): ExportSpecificationFactory
	{
		return new ExportSpecificationFactory();
	},
	'PDFCreator.ModuleFactory' => static function ( MediaWikiServices $services ): ExportModuleFactory {
		return new ExportModuleFactory(
			$services->getMainConfig(),
			$services->getObjectFactory()
		);
	},
	'PDFCreator.TemplateProviderFactory' => static function ( MediaWikiServices $services ): TemplateProviderFactory {
		return new TemplateProviderFactory(
			$services->getMainConfig(),
			$services->getObjectFactory()
		);
	},
	'PDFCreator.BackendFactory' => static function ( MediaWikiServices $services ): ExportBackendFactory {
		return new ExportBackendFactory(
			$services->getMainConfig(),
			$services->getObjectFactory()
		);
	},
	'PDFCreator.TargetFactory' => static function ( MediaWikiServices $services ): ExportTargetFactory {
		return new ExportTargetFactory(
			$services->getMainConfig(),
			$services->getObjectFactory()
		);
	},
	'PDFCreator.PageSpecFactory' => static function ( MediaWikiServices $services ): PageSpecFactory {
		return new PageSpecFactory(
			$services->getService( 'TitleFactory' ),
			$services->getService( 'RedirectLookup' ),
			$services->getService( 'PageProps' ),
			$services->getService( 'MainConfig' )
		);
	},
	'PDFCreator.ExportPageFactory' => static function ( MediaWikiServices $services ): ExportPageFactory {
		return new ExportPageFactory(
			$services->getService( 'PDFCreator.HtmlProviderFactory' ),
			$services->getService( 'PDFCreator.PageParamsFactory' )
		);
	},
	'PDFCreator.HtmlProviderFactory' => static function ( MediaWikiServices $services ): HtmlProviderFactory {
		return new HtmlProviderFactory(
			$services->getObjectFactory()
		);
	},
	'PDFCreator.MetaDataFactory' => static function ( MediaWikiServices $services ): MetaDataFactory {
		return new MetaDataFactory(
			$services->getObjectFactory()
		);
	},
	'PDFCreator.PreProcessorFactory' => static function ( MediaWikiServices $services ): ExportPreProcessorFactory {
		return new ExportPreProcessorFactory(
			$services->getObjectFactory()
		);
	},
	'PDFCreator.ProcessorFactory' => static function ( MediaWikiServices $services ): ExportProcessorFactory {
		return new ExportProcessorFactory(
			$services->getObjectFactory()
		);
	},
	'PDFCreator.PostProcessorFactory' => static function ( MediaWikiServices $services ): ExportPostProcessorFactory {
		return new ExportPostProcessorFactory(
			$services->getObjectFactory()
		);
	},
	'PDFCreator.PageParamsFactory' => static function ( MediaWikiServices $services ): PageParamsFactory {
		return new PageParamsFactory(
			$services->getObjectFactory()
		);
	},
	'PDFCreator.StylesheetsFactory' => static function ( MediaWikiServices $services ): StylesheetsFactory {
		return new StylesheetsFactory(
			$services->getObjectFactory()
		);
	},
	'PDFCreator.StyleBlocksFactory' => static function ( MediaWikiServices $services ): StyleBlocksFactory {
		return new StyleBlocksFactory(
			$services->getObjectFactory()
		);
	},
	'PDFCreator.WikiTemplateParser' => static function ( MediaWikiServices $services ): WikiTemplateParser {
		return new WikiTemplateParser(
			$services->getParserFactory(),
			$services->getTitleFactory(),
			$services->getContentLanguage(),
			RequestContext::getMain()
		);
	},
	'PDFCreator.MustacheTemplateParser' => static function ( MediaWikiServices $services ): MustacheTemplateParser {
		return new MustacheTemplateParser();
	},
	'PDFCreator.ExportHtmlBuilder' => static function ( MediaWikiServices $services ): ExportHtmlBuilder {
		return new ExportHtmlBuilder(
			$services->getContentLanguage(),
		);
	},
	'PDFCreator' => static function ( MediaWikiServices $services ): PDFCreator {
		$ModuleFactory = $services->getService( 'PDFCreator.ModuleFactory' );
		return new PDFCreator( $ModuleFactory );
	},
	'PDFCreator.Util' => static function ( MediaWikiServices $services ): PDFCreatorUtil {
		$templateProviderFactory = $services->getService( 'PDFCreator.TemplateProviderFactory' );
		return new PDFCreatorUtil(
			$services->getTitleFactory(),
			$templateProviderFactory
		);
	},
	'PDFCreator.ExportModeFactory' => static function ( MediaWikiServices $services ): ModeFactory {
		return new ModeFactory(
			$services->getObjectFactory(),
			RequestContext::getMain()
		);
	},
	'PDFCreator.MediaWikiCommonCssProvider' => static function (
		MediaWikiServices $services
	): MediaWikiCommonCssProvider {
		return new MediaWikiCommonCssProvider(
			$services->getTitleFactory(),
			$services->getRevisionLookup()
		);
	},
	'PDFCreator.TemplateValueExtractor' => static function ( MediaWikiServices $services ): TemplateValueExtractor {
		return new TemplateValueExtractor(
			$services->getService( 'PDFCreator.Util' ),
			$services->getRevisionLookup(),
			$services->getMainConfig()
		);
	},
	'PDFCreator.TemplateValueInsertor' => static function ( MediaWikiServices $services ): TemplateValueInsertor {
		return new TemplateValueInsertor(
			$services->getService( 'PDFCreator.Util' ),
			$services->getRevisionLookup(),
			$services->getWikiPageFactory()
		);
	}
];
