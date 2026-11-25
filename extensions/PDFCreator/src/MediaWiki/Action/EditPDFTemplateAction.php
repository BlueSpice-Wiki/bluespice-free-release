<?php

namespace MediaWiki\Extension\PDFCreator\MediaWiki\Action;

use EditAction;
use MediaWiki\Content\TextContent;
use MediaWiki\Extension\PDFCreator\MediaWiki\Content\PDFCreatorTemplate;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use OOUI\Element;
use OOUI\FieldsetLayout;
use OOUI\HtmlSnippet;
use OOUI\IndexLayout;
use OOUI\PanelLayout;
use OOUI\TabPanelLayout;
use OOUI\Theme;
use OOUI\Widget;
use OOUI\WikimediaUITheme;

class EditPDFTemplateAction extends EditAction {
	/**
	 * @return void
	 * @throws Exception
	 */
	public function show() {
		$this->checkCanExecute( $this->getUser() );
		$this->useTransactionalTimeLimit();
		Theme::setSingleton( new WikimediaUITheme() );
		Element::setDefaultDir( 'ltr' );
		$out = $this->getOutput();
		$out->setRobotPolicy( 'noindex,nofollow' );
		$out->setPageTitle(
			$this->getTitle()->getPrefixedText()
		);
		// The editor should always see the latest content when starting their edit.
		// Also to ensure cookie blocks can be set (T152462).
		$out->disableClientCache();
		$article = $this->getArticle();
		$revision = $article->fetchRevisionRecord();

		$util = MediaWikiServices::getInstance()->get( 'PDFCreator.Util' );
		$out->addModules( [ 'ext.pdfcreator.edit', 'ext.codeEditor.ace', 'ext.codeEditor.ace.modes' ] );

		$editSlots = $util->slots;
		$editSlots[] = 'main';
		foreach ( $editSlots as $slot ) {
			$content = new PDFCreatorTemplate( '' );
			$text = '';
			if ( $revision instanceof RevisionRecord ) {
				if ( $slot === 'main' ) {
					$content = $revision->getContent( $slot );
				} else {
					if ( $revision->hasSlot( $util->templatePrefix . $slot ) ) {
						$content = $revision->getContent( $util->templatePrefix . $slot );
					}
				}
			}
			if ( $content instanceof TextContent ) {
				$text = $content->getText();
			}

			$tabPanels[] = new TabPanelLayout( $slot, [
				'classes' => [ 'pdf-creator-template-tab-content' ],
				'label' => wfMessage( 'pdfcreator-tab-panel-' . $slot . '-label' )->text(),
				'content' => new FieldsetLayout( [
					'classes' => [ 'pdf-creator-template-tab-fieldset' ],
					'items' => [
						new Widget( [
							'content' => new HtmlSnippet( Html::element( 'textarea', [
								'id' => 'pdfcreator_template_' . $slot,
								'name' => 'pdfcreator_template_' . $slot,
								'rows' => 10,
								'cols' => 50,
								'data-editor' => 'html'
							], $text ) )
						] ),
					],
					'padded' => false,
					'expanded' => false,
				] ),
				'padded' => false,
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
		$panel = new PanelLayout( [
			'framed' => true,
			'expanded' => false,
			'classes' => [ 'pdf-creator-template-tabs-wrapper' ],
			'content' => $indexLayout
		] );
		$out->addModuleStyles( [ 'ext.pdfcreator.skeleton.styles' ] );
		$skeleton = $util->buildTabPanelSkeleton();
		$out->addHtml(
			$skeleton .
			'<div id="pdf-creator-template-cnt" style="display:none;">' .
			'<div id="pdf-creator-toolbar"></div>' .
			$panel->toString() .
			'</div>'
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getRestriction() {
		return 'edit';
	}

}
