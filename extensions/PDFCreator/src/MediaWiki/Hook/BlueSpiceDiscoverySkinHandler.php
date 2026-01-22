<?php

namespace MediaWiki\Extension\PDFCreator\MediaWiki\Hook;

use MediaWiki\Extension\PDFCreator\Component\ExportDialogButton;
use MediaWiki\Message\Message;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\SpecialPage\SpecialPageFactory;
use MediaWiki\Title\NamespaceInfo;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\RestrictedTextLink;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class BlueSpiceDiscoverySkinHandler implements MWStakeCommonUIRegisterSkinSlotComponents {

	/** @var NamespaceInfo */
	private $namespaceInfo;

	/** @var PermissionManager */
	private $permissionManager;

	/** @var SpecialPageFactory */
	private $specialPageFactory;

	/**
	 * @param NamespaceInfo $namespaceInfo
	 * @param PermissionManager $permissionManager
	 * @param SpecialPageFactory $specialPageFactory
	 */
	public function __construct( NamespaceInfo $namespaceInfo, PermissionManager $permissionManager,
		SpecialPageFactory $specialPageFactory ) {
		$this->namespaceInfo = $namespaceInfo;
		$this->permissionManager = $permissionManager;
		$this->specialPageFactory = $specialPageFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function onMWStakeCommonUIRegisterSkinSlotComponents( $registry ): void {
		$namespaceInfo = $this->namespaceInfo;
		$permissionManager = $this->permissionManager;
		$registry->register(
			'ToolbarPanel',
			[
				'export1' => [
					'factory' => static function () use ( $namespaceInfo, $permissionManager ) {
						return new ExportDialogButton( $namespaceInfo, $permissionManager );
					}
				]
			]
		);

		$specialOverview = $this->specialPageFactory->getTitleForAlias( 'PDFTemplatesOverview' );
		$overviewEntry = [
			'bs-special-pdf-templates' => [
				'factory' => static function () use ( $specialOverview ) {
					return new RestrictedTextLink( [
						'id' => 'ga-bs-pdf-templates',
						'href' => $specialOverview->getLocalURL(),
						'text' => Message::newFromKey( 'pdfcreator-global-action-overview' ),
						'title' => Message::newFromKey( 'pdfcreator-global-action-overview-desc' ),
						'aria-label' => Message::newFromKey( 'pdfcreator-global-action-overview' ),
						'permissions' => [ 'read' ]
					] );
				}
			]
		];
		$registry->register( 'GlobalActionsOverview', $overviewEntry );
	}
}
