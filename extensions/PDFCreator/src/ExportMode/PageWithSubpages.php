<?php

namespace MediaWiki\Extension\PDFCreator\ExportMode;

class PageWithSubpages extends Page {

	/**
	 *
	 * @inheritDoc
	 */
	public function getKey(): string {
		return 'pageWithSubpages';
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getLabel(): string {
		return 'pdfcreator-export-plugin-mode-option-subpages-label';
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
		if ( isset( $data['revId'] ) ) {
			unset( $data['revId'] );
		}

		$pages[] = [
			'type' => 'page',
			'target' => $title->getPrefixedDBkey(),
			'params' => $params
		];
		$subModulePages = $title->getSubpages();
		foreach ( $subModulePages as $subPage ) {
			$pages[] = [
				'type' => 'page',
				'target' => $subPage->getPrefixedDBkey(),
				'params' => $data
			];
		}
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
		if ( count( $title->getSubpages() ) < 1 ) {
			return false;
		}
		return true;
	}

}
