<?php

namespace MediaWiki\Extension\PDFCreator\Component;

use MediaWiki\Context\IContextSource;
use MediaWiki\Message\Message;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\NamespaceInfo;
use MWStake\MediaWiki\Component\CommonUserInterface\Component\SimpleLink;

class ExportDialogButton extends SimpleLink {

	/** @var NamespaceInfo */
	private $namespaceInfo;

	/** @var PermissionManager */
	private $permissionManager;

	/**
	 * @param NamespaceInfo $namespaceInfo
	 * @param PermissionManager $permissionManager
	 */
	public function __construct( NamespaceInfo $namespaceInfo, PermissionManager $permissionManager ) {
		parent::__construct( [
			'id' => 'pdfcreator-export-dlg',
			'role' => 'button',
			'classes' => [ 'ico-btn', 'bi-file-earmark' ],
			'href' => '',
			'title' => Message::newFromKey( 'pdfcreator-dialog-button-title' ),
			'aria-label' => Message::newFromKey( 'pdfcreator-dialog-button-text' ),
			'rel' => 'nofollow'
		] );
		$this->namespaceInfo = $namespaceInfo;
		$this->permissionManager = $permissionManager;
	}

	/**
	 * @inheritDoc
	 */
	public function shouldRender( IContextSource $context ): bool {
		$title = $context->getTitle();
		if ( !$title ) {
			return false;
		}
		if ( $title->getArticleId() < 1 ) {
			return false;
		}
		if ( !$this->namespaceInfo->isContent( $title->getNamespace() ) ) {
			return false;
		}
		if ( !$this->permissionManager->userCan( 'read', $context->getUser(), $title ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getRequiredRLModules(): array {
		return [ 'ext.pdfcreator.export' ];
	}
}
