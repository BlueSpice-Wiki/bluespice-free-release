<?php

namespace MediaWiki\Extension\PDFCreator\ExportMode;

use MediaWiki\Config\Config;
use MediaWiki\Extension\PDFCreator\IExportMode;
use MediaWiki\Title\TitleFactory;

class Page implements IExportMode {

	/** @var Config */
	protected $config;

	/** @var TitleFactory */
	protected $titleFactory;

	/**
	 * @param Config $config
	 * @param TitleFactory $titleFactory
	 */
	public function __construct( Config $config, TitleFactory $titleFactory ) {
		$this->config = $config;
		$this->titleFactory = $titleFactory;
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
		$pages[] = [
			'type' => 'page',
			'target' => $title->getPrefixedDBkey(),
			'rev-id' => isset( $data['revId'] ) ? $data['revId'] : $title->getLatestRevID(),
			'params' => $data
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

}
