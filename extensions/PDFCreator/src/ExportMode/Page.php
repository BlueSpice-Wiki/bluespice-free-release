<?php

namespace MediaWiki\Extension\PDFCreator\ExportMode;

use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;
use MediaWiki\Extension\PDFCreator\IContextSourceAware;
use MediaWiki\Extension\PDFCreator\IExportMode;
use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;

class Page implements IExportMode, IContextSourceAware {

	/** @var Config */
	protected $config;

	/** @var TitleFactory */
	protected $titleFactory;

	/** @var PermissionManager */
	protected $permissionManager;

	/** @var IContextSource */
	protected $context;

	/**
	 * @param Config $config
	 * @param TitleFactory $titleFactory
	 * @param PermissionManager|null $permissionManager
	 */
	public function __construct( Config $config, TitleFactory $titleFactory,
		?PermissionManager $permissionManager = null ) {
		$this->config = $config;
		$this->titleFactory = $titleFactory;
		if ( !$permissionManager ) {
			$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
		}
		$this->permissionManager = $permissionManager;
	}

	/**
	 * @param IContextSource $context
	 * @return void
	 */
	public function setContext( IContextSource $context ): void {
		$this->context = $context;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getKey(): string {
		return 'page';
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getLabel(): string {
		return 'pdfcreator-export-mode-page-label';
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getRLModules(): array {
		return [];
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function applies( $format ): bool {
		return ( $format === $this->getKey() ) ? true : false;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getExportPages( $title, $data ): array {
		$revId = isset( $data['revId'] ) ? $data['revId'] : $title->getLatestRevID();
		$params = $data;
		$params['rev-id'] = $revId;
		if ( isset( $params['revId'] ) ) {
			unset( $params['revId'] );
		}
		if ( !$this->userCanReadPage( $title ) ) {
			return [];
		}

		$pages[] = [
			'type' => 'page',
			'target' => $title->getPrefixedDBkey(),
			'params' => $params
		];
		return $pages;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function isRelevantExportMode( $title ): bool {
		if ( !$title->exists() ) {
			return false;
		}
		return true;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getDefaultTemplate(): string {
		$template = $this->config->get( 'PDFCreatorDefaultTemplate' );
		$templateTitle = $this->titleFactory->newFromText( 'MediaWiki:PDFCreator/' . $template );
		if ( !$templateTitle->exists() ) {
			return '';
		}
		return $template;
	}

	/**
	 * @param Title $title
	 * @return bool
	 */
	protected function userCanReadPage( $title ) {
		$user = $this->context->getUser();
		if ( $this->permissionManager->userCan( 'read', $user, $title ) ) {
			return true;
		}
		return false;
	}

}
