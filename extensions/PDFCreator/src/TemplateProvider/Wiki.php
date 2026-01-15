<?php

namespace MediaWiki\Extension\PDFCreator\TemplateProvider;

use MediaWiki\Config\Config;
use MediaWiki\Extension\PDFCreator\ITemplateProvider;
use MediaWiki\Extension\PDFCreator\PDFCreatorUtil;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Extension\PDFCreator\Utility\LessVarsReplacer;
use MediaWiki\Extension\PDFCreator\Utility\Template;
use MediaWiki\Extension\PDFCreator\Utility\TemplateResources;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Title\TitleFactory;

class Wiki implements ITemplateProvider {

	/** @var Config */
	private $config;

	/** @var PDFCreatorUtil */
	private $util;

	/** @var RevisionLookup */
	private $revisionLookup;

	/** @var TitleFactory */
	private $titleFactory;

	/** @var array */
	private $templateContent = [];

	/**
	 * @param Config $config
	 * @param PDFCreatorUtil $util
	 * @param RevisionLookup $revisionLookup
	 * @param TitleFactory $titleFactory
	 */
	public function __construct( Config $config, PDFCreatorUtil $util,
		RevisionLookup $revisionLookup, TitleFactory $titleFactory ) {
		$this->config = $config;
		$this->util = $util;
		$this->revisionLookup = $revisionLookup;
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @return array
	 */
	public function getTemplateNames(): array {
		$templateNames = $this->util->getAllWikiTemplates();
		return $templateNames;
	}

	/**
	 * @param ExportContext $context
	 * @param string $name
	 * @return Template|null
	 */
	public function getTemplate( ExportContext $context, string $name = '' ): ?Template {
		$template = null;

		$templateTitle = $this->util->createPDFTemplateTitle( $name );
		$revId = $templateTitle->getLatestRevID();

		$revision = $this->revisionLookup->getRevisionByTitle( $templateTitle, $revId );
		$this->templateContent = [];
		foreach ( $this->util->slots as $slot ) {
			$content = $revision->getContent( $this->util->templatePrefix . $slot );
			$this->templateContent[ $slot ][] = $content;
		}

		$intro = isset( $this->templateContent['intro'][0] ) ? $this->templateContent['intro'][0]->getText() : '';
		$outro = isset( $this->templateContent['outro'][0] ) ? $this->templateContent['outro'][0]->getText() : '';
		$header = isset( $this->templateContent['header'][0] ) ? $this->templateContent['header'][0]->getText() : '';
		$footer = isset( $this->templateContent['footer'][0] ) ? $this->templateContent['footer'][0]->getText() : '';
		$body = isset( $this->templateContent['body'][0] ) ? $this->templateContent['body'][0]->getText() : '';
		$optionsJson = isset( $this->templateContent['options'][0] ) ? $this->templateContent['options'][0]->getText() : ''; // phpcs:ignore Generic.Files.LineLength.TooLong
		$options = json_decode( $optionsJson, true );

		$template = new Template(
			$body, $header, $footer, $intro, $outro,
			$this->getResources( $name ),
			[],
			$options ? $options : []
		);

		return $template;
	}

	/**
	 * @param string $name
	 * @return TemplateResources
	 */
	private function getResources( string $name ): TemplateResources {
		$styles = isset( $this->templateContent['styles'][0] ) ? $this->templateContent['styles'][0]->getText() : '';
		$lessVarReplacer = new LessVarsReplacer();
		$styles = $lessVarReplacer->replaceLessVars( $styles );
		$styleBlocks = ( $styles !== '' ) ? [ $name => $styles ] : [];
		$imagePaths = [];
		$logos = $this->config->get( 'Logos' );
		if ( isset( $logos['1x'] ) ) {
			$logoUrl = $logos['1x'];
			$logoPathParts = explode( '/', $logoUrl );
			$count = count( $logoPathParts );
			$logoName = $logoPathParts[ $count - 1 ];
			$fileTitle = $this->titleFactory->newFromText( $logoName, NS_FILE );
			if ( !$fileTitle->exists() ) {
				$scriptPath = $this->config->get( 'ScriptPath' );
				$imagePath = str_replace( $scriptPath, '', $logoUrl );
				$url = MW_INSTALL_PATH . $imagePath;
				$imagePaths[$logoName] = $url;
			}
		}

		$commonDir = MW_INSTALL_PATH . '/extensions/PDFCreator/data/common';

		return new TemplateResources(
			[
				'DejaVuSans-Bold.ttf' => $commonDir . '/fonts/DejaVuSans-Bold.ttf',
				'DejaVuSans-BoldOblique.ttf' => $commonDir . '/fonts/DejaVuSans-BoldOblique.ttf',
				'DejaVuSans-Oblique.ttf' => $commonDir . '/fonts/DejaVuSans-Oblique.ttf',
				'DejaVuSans.ttf' => $commonDir . '/fonts/DejaVuSans.ttf',
				'DejaVuSansMono-Bold.ttf' => $commonDir . '/fonts/DejaVuSansMono-Bold.ttf',
				'DejaVuSansMono-BoldOblique.ttf' => $commonDir . '/fonts/DejaVuSansMono-BoldOblique.ttf',
				'DejaVuSansMono-Oblique.ttf' => $commonDir . '/fonts/DejaVuSansMono-Oblique.ttf',
				'DejaVuSansMono.ttf' => $commonDir . '/fonts/DejaVuSansMono.ttf',
			],
			[
				'mediawiki.css' => $commonDir . '/stylesheets/mediawiki.css',
				'geshi-php.css' => $commonDir . '/stylesheets/geshi-php.css',
				'bluespice.css' => $commonDir . '/stylesheets/bluespice.css',
				'tables.css' => $commonDir . '/stylesheets/tables.css',
				'fonts.css' => $commonDir . '/stylesheets/fonts.css',
				'mediawiki.action.history.diff.css' => $commonDir . '/stylesheets/mediawiki.action.history.diff.css',
				'page.css' => $commonDir . '/stylesheets/page.css',
			],
			$styleBlocks,
			$imagePaths
		);
	}

}
