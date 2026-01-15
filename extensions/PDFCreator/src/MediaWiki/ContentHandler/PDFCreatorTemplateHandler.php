<?php

namespace MediaWiki\Extension\PDFCreator\MediaWiki\ContentHandler;

use MediaWiki\Content\Content;
use MediaWiki\Content\Renderer\ContentParseParams;
use MediaWiki\Content\TextContent;
use MediaWiki\Content\TextContentHandler;
use MediaWiki\Extension\PDFCreator\MediaWiki\Action\EditPDFTemplateAction;
use MediaWiki\Extension\PDFCreator\MediaWiki\Content\PDFCreatorTemplate;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\ParserFactory;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Registration\ExtensionRegistry;
use OOUI\Element;
use OOUI\FieldsetLayout;
use OOUI\HtmlSnippet;
use OOUI\IndexLayout;
use OOUI\PanelLayout;
use OOUI\TabPanelLayout;
use OOUI\Theme;
use OOUI\Widget;
use OOUI\WikimediaUITheme;

class PDFCreatorTemplateHandler extends TextContentHandler {

	/** @var RevisionLookup */
	private $revisionLookup;

	/** @var TitleFactory */
	private $titleFactory;

	/** @var ParserFactory */
	private $parserFactory;

	/** @var PDFCreatorUtil */
	private $util;

	/**
	 * @param string|null $modelId
	 */
	public function __construct( $modelId = 'pdfcreator_template' ) {
		parent::__construct( $modelId, [ CONTENT_FORMAT_HTML ] );

		$services = MediaWikiServices::getInstance();
		$this->revisionLookup = $services->getRevisionLookup();
		$this->titleFactory = $services->getTitleFactory();
		$this->parserFactory = $services->getParserFactory();
		$this->util = $services->get( 'PDFCreator.Util' );
	}

	/**
	 * @return string
	 */
	protected function getContentClass() {
		return PDFCreatorTemplate::class;
	}

	/**
	 * @return false
	 */
	public function supportsSections() {
		return false;
	}

	/**
	 * @return bool
	 */
	public function supportsCategories() {
		return true;
	}

	/**
	 * @return false
	 */
	public function supportsRedirects() {
		return false;
	}

	/**
	 * @return string[]
	 */
	public function getActionOverrides() {
		return [
			'edit' => EditPDFTemplateAction::class,
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function fillParserOutput(
		Content $content,
		ContentParseParams $cpoParams,
		ParserOutput &$output
	) {
		Theme::setSingleton( new WikimediaUITheme() );
		Element::setDefaultDir( 'ltr' );
		$pageRef = $cpoParams->getPage();
		$revId = $cpoParams->getRevId();
		$title = $this->titleFactory->castFromPageReference( $pageRef );
		$revision = $this->revisionLookup->getRevisionByTitle( $title, $revId );
		if ( !$revision ) {
			return $output->setRawText( 'No revision' );
		}

		$tabPanels = [];
		foreach ( $this->util->slots as $slot ) {
			$content = $revision->getContent( $this->util->templatePrefix . $slot );
			if ( !$content instanceof TextContent ) {
				continue;
			}
			$text = $this->getContent( $content, $cpoParams );
			$tabPanels[] = new TabPanelLayout( $slot, [
				'classes' => [ 'pdf-creator-template-tab-' . $slot ],
				'label' => wfMessage( 'pdfcreator-tab-panel-' . $slot . '-label' )->plain(),
				'content' => new FieldsetLayout( [
					'classes' => [ 'pdf-creator-template-tab-fieldset' ],
					'items' => [
						new Widget( [
							'content' => $text
						] )
					],
				] ),
				'expanded' => false,
				'framed' => true
			] );
		}

		$indexLayout = new IndexLayout( [
			'infusable' => true,
			'expanded' => false,
			'autoFocus' => false,
			'classes' => [ 'pdf-creator-template-tab' ],
		] );
		$indexLayout->addTabPanels( $tabPanels );
		$indexLayout->setInfusable( true );

		$description = new FieldsetLayout( [
			'classes' => [ 'pdf-creator-template-description' ],
			'items' => [
				new Widget( [
					'content' => $revision->getContent( 'main' )->getText()
				] )
			]
		] );

		$output->addModuleStyles( [ 'ext.pdfcreator.skeleton.styles' ] );
		$skeleton = $this->util->buildTabPanelSkeleton();
		$output->addModules( [ 'ext.pdfcreator.templates' ] );

		$panel = new PanelLayout( [
			'framed' => true,
			'expanded' => false,
			'classes' => [ 'pdf-creator-template-tabs-wrapper' ]
		] );
		$panel->appendContent( $indexLayout );
		$outputText = $skeleton . '<div id="pdf-creator-template-cnt" style="display:none;">' .
			$description . $panel . '</div>';

		$output->setRawText( $outputText );
	}

	/**
	 *
	 * @param TextContent $content
	 * @param ContentParseParams $cpoParams
	 * @return string
	 */
	private function getContent( $content, $cpoParams ) {
		$text = $content->getText();
		if ( !ExtensionRegistry::getInstance()->isLoaded( 'SyntaxHighlight' ) ) {
			return $text;
		}
		$parser = $this->parserFactory->getInstance();
		$parser->parse(
			'<syntaxhighlight lang="html" line>' . $text . '</syntaxhighlight>',
			$cpoParams->getPage(),
			$cpoParams->getParserOptions(),
			true,
			true,
			$cpoParams->getRevId()
		);
		$output = $parser->getOutput()->runOutputPipeline(
			$cpoParams->getParserOptions()
		);
		$parsedContent = $output->getContentHolderText();
		return new HtmlSnippet( $parsedContent );
	}
}
