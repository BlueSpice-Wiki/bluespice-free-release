<?php

namespace MediaWiki\Extension\PDFCreator\MediaWiki\Special;

use MediaWiki\Html\Html;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\TitleFactory;
use OOJSPlus\Special\OOJSGridSpecialPage;

class PDFTemplatesOverview extends OOJSGridSpecialPage {

	/** @var PermissionManager */
	private $permissionManager;

	/** @var TitleFactory */
	private $titleFactory;

	/**
	 * @param PermissionManager $permissionManager
	 * @param TitleFactory $titleFactory
	 */
	public function __construct( PermissionManager $permissionManager, TitleFactory $titleFactory ) {
		parent::__construct( 'PDFTemplatesOverview', 'edit' );
		$this->permissionManager = $permissionManager;
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function doExecute( $subpage ) {
		$title = $this->titleFactory->newFromText( 'PDFCreator', NS_MEDIAWIKI );
		$user = $this->getUser();
		$userCanEdit = $this->permissionManager->userCan( 'edit', $user, $title );
		$this->getOutput()->addModules( [ 'ext.pdfcreator.special.overview' ] );
		$this->getOutput()->addHTML(
			Html::element( 'div', [
				'id' => 'pdfcreator-overview',
				'data-edit' => $userCanEdit
			] ) );
	}
}
