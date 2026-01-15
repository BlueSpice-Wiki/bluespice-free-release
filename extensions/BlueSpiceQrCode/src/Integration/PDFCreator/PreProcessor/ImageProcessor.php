<?php

namespace BlueSpice\QrCode\Integration\PDFCreator\PreProcessor;

use BlueSpice\QrCode\Integration\PDFCreator\Utility\ImageFinder;
use MediaWiki\Extension\PDFCreator\IPreProcessor;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Extension\PDFCreator\Utility\ImageUrlUpdater;
use MediaWiki\Extension\PDFCreator\Utility\ImageWidthUpdater;
use MediaWiki\Title\TitleFactory;
use MediaWiki\Utils\UrlUtils;
use RepoGroup;

class ImageProcessor implements IPreProcessor {

	/** @var UrlUtils */
	private $urlUtils;

	/** @var TitleFactory */
	private $titleFactory;

	/** @var RepoGroup */
	private $repoGroup;

	/**
	 * @param UrlUtils $urlUtils
	 * @param TitleFactory $titleFactory
	 * @param RepoGroup $repoGroup
	 */
	public function __construct(
		UrlUtils $urlUtils,
		TitleFactory $titleFactory,
		RepoGroup $repoGroup
	) {
		$this->urlUtils = $urlUtils;
		$this->titleFactory = $titleFactory;
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
		$imageFinder = new ImageFinder(
			$this->urlUtils, $this->titleFactory, $this->repoGroup
		);
		$results = $imageFinder->execute( $pages, $images );

		$AttachmentUrlUpdater = new ImageUrlUpdater( $this->titleFactory );
		$AttachmentUrlUpdater->execute( $pages, $results );

		$imageWidthUpdater = new ImageWidthUpdater();
		$imageWidthUpdater->execute( $pages );

		/** @var WikiFileResource */
		foreach ( $results as $result ) {
			$filename = $result->getFilename();
			$images[$filename] = $result->getAbsolutePath();
		}
	}
}
