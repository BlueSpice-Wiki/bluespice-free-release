<?php

namespace MediaWiki\Extension\ImageMapEdit;

use MediaWiki\MediaWikiServices;
use MediaWiki\Output\OutputPage;
use MediaWiki\Parser\ParserOutput;

class ImageMapEdit {

	/**
	 * @param ParserOutput &$parserOutput
	 * @param string &$text
	 * @return void
	 */
	public static function onOutputPageBeforeHTML( &$parserOutput, &$text ) {
		$currentTitle = $parserOutput->getTitle();
		if ( $currentTitle === null
			|| $currentTitle->getNamespace() != NS_FILE
			|| $parserOutput->getRequest()->getVal( 'action', 'view' ) != 'view' ) {
			return true;
		}
		$repoGroup = MediaWikiServices::getInstance()->getRepoGroup();
		$currentFile = $repoGroup->findFile( $currentTitle );
		if ( $currentFile && !$currentFile->canRender() ) {
			return true;
		}
		return true;
	}

	/**
	 *
	 * @param OutputPage &$outputPage
	 * @param Skin &$skin
	 *
	 * @return bool
	 */
	public static function onBeforePageDisplay( &$outputPage, &$skin ) {
		if ( $outputPage->getTitle()->getNamespace() != NS_FILE
			|| $outputPage->getRequest()->getVal( 'action', 'view' ) != 'view' ) {
			return true;
		}

		$user = $skin->getUser();
		$title = $skin->getTitle();

		$titleParts = explode( '.', $title->getText() );
		$fileType = $titleParts[count( $titleParts ) - 1];

		$userCanEdit = MediaWikiServices::getInstance()->getPermissionManager()->userCan( 'edit', $user, $title );
		if ( !$userCanEdit || !in_array( $fileType, $GLOBALS['imeFileTypeList'] ) ) {
			return true;
		}

		$outputPage->addModules( 'ext.imagemapedit' );

		return true;
	}
}
