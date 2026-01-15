<?php

namespace MediaWiki\Extension\PDFCreator\MediaWiki\Maintenance;

use Exception;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Content\CssContent;
use MediaWiki\Content\JsonContent;
use MediaWiki\Extension\PDFCreator\MediaWiki\Content\PDFCreatorTemplate;
use MediaWiki\Maintenance\LoggedUpdateMaintenance;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\User\User;

require_once __DIR__ . '/../../../../../maintenance/Maintenance.php';

class AddDefaultPDFTemplates extends LoggedUpdateMaintenance {

	/**
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'pdf-creator-default-pdf-creation';
	}

	/**
	 * @return bool|void
	 * @throws Exception
	 */
	protected function doDBUpdates() {
		$this->output( "Adding default pdf templates...\n" );

		$baseDir = __DIR__ . '/../../../data/PDFTemplates';
		$templates = [
			'pdfcreator_template_intro' => [
				'file' => $baseDir . '/Intro.html',
			],
			'pdfcreator_template_body' => [
				'file' => $baseDir . '/Body.html',
			],
			'pdfcreator_template_header' => [
				'file' => $baseDir . '/Header.html',
			],
			'pdfcreator_template_footer' => [
				'file' => $baseDir . '/Footer.html',
			],
			'pdfcreator_template_outro' => [
				'file' => $baseDir . '/Outro.html',
			],
			'pdfcreator_template_styles' => [
				'file' => $baseDir . '/Styles.css',
			],
			'pdfcreator_template_options' => [
				'file' => $baseDir . '/Options.json',
			]
		];

		// No injection possible :(
		$services = MediaWikiServices::getInstance();
		$titleFactory = $services->getTitleFactory();
		$wikiPageFactory = $services->getWikiPageFactory();

		$title = $titleFactory->newFromText( 'PDFCreator/StandardPDF', NS_MEDIAWIKI );
		$wikiPage = $wikiPageFactory->newFromTitle( $title );
		$updater = $wikiPage->newPageUpdater( $this->getMaintenanceUser() );
		$updater->setContent( 'main', new PDFCreatorTemplate( '' ) );
		foreach ( $templates as $slotKey => $template ) {
			$content = file_get_contents( $template['file'] );
			if ( $slotKey === 'pdfcreator_template_styles' ) {
				$content = new CssContent( $content );
			} elseif ( $slotKey === 'pdfcreator_template_options' ) {
				$content = new JsonContent( $content );
			} else {
				$content = new PDFCreatorTemplate( $content );
			}
			$updater->setContent( $slotKey, $content );
		}
		$rev = $updater->saveRevision(
			CommentStoreComment::newUnsavedComment( 'Default pdf template content' )
		);
		if ( $rev instanceof RevisionRecord ) {
			$this->output( "done\n" );
		} else {
			$this->output( "failed. {$updater->getStatus()->getMessage()->text()}\n" );
		}
		return true;
	}

	/**
	 * @return User
	 */
	private function getMaintenanceUser(): User {
		return User::newSystemUser( 'MediaWiki default', [ 'steal' => true ] );
	}
}

$maintClass = AddDefaultPDFTemplates::class;
require_once RUN_MAINTENANCE_IF_MAIN;
