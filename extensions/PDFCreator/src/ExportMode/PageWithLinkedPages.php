<?php

namespace MediaWiki\Extension\PDFCreator\ExportMode;

use MediaWiki\Config\Config;
use MediaWiki\Title\NamespaceInfo;
use MediaWiki\Title\TitleFactory;
use Wikimedia\Rdbms\LoadBalancer;

class PageWithLinkedPages extends Page {

	/** @var LoadBalancer */
	protected $loadBalancer;

	/** @var NamespaceInfo */
	protected $namespaceInfo;

	/**
	 *
	 * @param Config $config
	 * @param TitleFactory $titleFactory
	 * @param LoadBalancer $loadBalancer
	 * @param NamespaceInfo $namespaceInfo
	 */
	public function __construct(
		Config $config, TitleFactory $titleFactory, LoadBalancer $loadBalancer,
		NamespaceInfo $namespaceInfo
	) {
		parent::__construct( $config, $titleFactory );
		$this->loadBalancer = $loadBalancer;
		$this->namespaceInfo = $namespaceInfo;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getKey(): string {
		return 'pageWithLinkedPages';
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function getLabel(): string {
		return 'pdfcreator-export-plugin-mode-option-recursive-label';
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
			'rev-id' => isset( $data['revId'] ) ? $data['revId'] : $title->getLatestRevID()
		];
		$db = $this->loadBalancer->getConnection( DB_REPLICA );
		$res = $db->select(
			[ 'pagelinks', 'linktarget' ],
			[ 'pl_target_id', 'lt_namespace', 'lt_title' ],
			[
				'pl_from' => $title->getArticleID()
			],
			__METHOD__,
			[],
			[
				'linktarget' => [
					'LEFT JOIN', [ 'pl_target_id = lt_id' ]
				]
			]
		);
		foreach ( $res as $linkText ) {
			$subTitle = $this->titleFactory->newFromText( $linkText->lt_title, $linkText->lt_namespace );
			if ( !$subTitle ) {
				continue;
			}

			if ( !$this->namespaceInfo->isContent( $subTitle->getNamespace() ) ) {
				continue;
			}
			$pages[] = [
				'type' => 'page',
				'target' => $subTitle->getPrefixedDBkey(),
				'params' => $data
			];
		}
		return $pages;
	}
}
