<?php

namespace MediaWiki\Extension\PDFCreator\Processor;

use MediaWiki\Config\Config;
use MediaWiki\Extension\PDFCreator\IProcessor;
use MediaWiki\Extension\PDFCreator\Utility\AttachmentFinder;
use MediaWiki\Extension\PDFCreator\Utility\AttachmentUrlUpdater;
use MediaWiki\Extension\PDFCreator\Utility\BoolValueGet;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Extension\PDFCreator\Utility\ExportPage;
use MediaWiki\Extension\PDFCreator\Utility\WikiFileResource;
use MediaWiki\Title\TitleFactory;
use RepoGroup;

class AttachmentProcessor implements IProcessor {

	/** @var TitleFactory */
	private $titleFactory;

	/** @var Config */
	private $config;

	/** @var RepoGroup */
	private $repoGroup;

	/**
	 * @param TitleFactory $titleFactory
	 * @param Config $config
	 * @param RepoGroup $repoGroup
	 */
	public function __construct(
		TitleFactory $titleFactory, Config $config, RepoGroup $repoGroup ) {
		$this->titleFactory = $titleFactory;
		$this->config = $config;
		$this->repoGroup = $repoGroup;
	}

	/**
	 * @param ExportPage[] &$pages
	 * @param array &$images
	 * @param array &$attachments
	 * @param ExportContext|null $context
	 * @param string $module
	 * @param array $params
	 * @return void
	 */
	public function execute(
		array &$pages, array &$images, array &$attachments,
		?ExportContext $context = null, string $module = '', $params = []
	): void {
		if ( !isset( $params['attachments'] ) || !BoolValueGet::from( $params['attachments'] ) ) {
			return;
		}
		$attachmentFinder = new AttachmentFinder(
			$this->titleFactory, $this->config, $this->repoGroup
		);

		$results = $attachmentFinder->execute( $pages, $attachments );

		$attachmentUrlUpdater = new AttachmentUrlUpdater();
		$attachmentUrlUpdater->execute( $pages, $results );

		/** @var WikiFileResource */
		foreach ( $results as $result ) {
			$filename = $result->getFilename();
			$attachments[$filename] = $result->getAbsolutePath();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getPosition(): int {
		return 95;
	}
}
