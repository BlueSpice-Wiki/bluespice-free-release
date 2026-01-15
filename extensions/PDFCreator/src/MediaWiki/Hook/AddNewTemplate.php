<?php

namespace MediaWiki\Extension\PDFCreator\MediaWiki\Hook;

use BlueSpice\Discovery\Hook\BlueSpiceDiscoveryTemplateDataProviderAfterInit;
use BlueSpice\Discovery\ITemplateDataProvider;
use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\TitleFactory;
use SkinTemplate;

class AddNewTemplate implements SkinTemplateNavigation__UniversalHook, BlueSpiceDiscoveryTemplateDataProviderAfterInit {

	/** @var PermissionManager */
	private $permissionManager;

	/** @var TitleFactory */
	private $titleFactory;

	/**
	 *
	 * @param PermissionManager $permissionManager
	 * @param TitleFactory $titleFactory
	 */
	public function __construct( PermissionManager $permissionManager, TitleFactory $titleFactory ) {
		$this->permissionManager = $permissionManager;
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @param SkinTemplate $sktemplate
	 * @return bool
	 */
	protected function skipProcessing( SkinTemplate $sktemplate ) {
		$user = $sktemplate->getUser();
		$title = $this->titleFactory->newFromText( 'PDFCreator', NS_MEDIAWIKI );

		$userCan = $this->permissionManager->userCan( 'edit', $user, $title );
		if ( !$userCan ) {
			return true;
		}
		return false;
	}

	/**
	 * // phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
	 * @inheritDoc
	 */
	public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
		if ( $this->skipProcessing( $sktemplate ) ) {
			return;
		}
		$title = $sktemplate->getTitle();
		if ( $title->isSpecial( 'PDFTemplatesOverview' ) ) {
			$links['actions']['pdfcreator-create-new-template'] = [
				'text' => $sktemplate->msg( 'pdfcreator-action-create-template-text' )->plain(),
				'title' => $sktemplate->msg( 'pdfcreator-action-create-template-title' )->plain(),
				'href' => '',
				'class' => 'new-template-action',
				'id' => 'ca-pdfcreator-create-new-template',
				'position' => 1,
			];

			$sktemplate->getOutput()->addModules( 'ext.pdfcreator.template.edit' );
		}
	}

	/**
	 *
	 * @param ITemplateDataProvider $registry
	 * @return void
	 */
	public function onBlueSpiceDiscoveryTemplateDataProviderAfterInit( $registry ): void {
		$registry->register( 'actions_primary', 'ca-pdfcreator-create-new-template' );
	}
}
