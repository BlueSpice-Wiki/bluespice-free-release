<?php

namespace MediaWiki\Extension\PDFCreator\MediaWiki\Hook;

use MediaWiki\Extension\PDFCreator\Component\ExportDialogButton;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\NamespaceInfo;
use MWStake\MediaWiki\Component\CommonUserInterface\Hook\MWStakeCommonUIRegisterSkinSlotComponents;

class BlueSpiceDiscoverySkinHandler implements MWStakeCommonUIRegisterSkinSlotComponents {

	/** @var NamespaceInfo */
	private $namespaceInfo;

	/** @var PermissionManager */
	private $permissionManager;

	/**
	 * @param NamespaceInfo $namespaceInfo
	 * @param PermissionManager $permissionManager
	 */
	public function __construct( NamespaceInfo $namespaceInfo, PermissionManager $permissionManager ) {
		$this->namespaceInfo = $namespaceInfo;
		$this->permissionManager = $permissionManager;
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
	}
}
