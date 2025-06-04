<?php

namespace BlueSpice\Avatars\Integration\PDFCreator\PreProcessor;

use BlueSpice\Avatars\Integration\PDFCreator\Utility\ImageFinder;
use MediaWiki\Extension\PDFCreator\IPreProcessor;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Extension\PDFCreator\Utility\ImageUrlUpdater;
use MediaWiki\Extension\PDFCreator\Utility\ImageWidthUpdater;
use MediaWiki\Extension\UserProfile\ProfileImage\ProfileImageProviderFactory;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\UserFactory;
use MediaWiki\Utils\UrlUtils;

class ImageProcessor implements IPreProcessor {

	/** @var UrlUtils */
	private $urlUtils;

	/** @var UserFactory */
	private $userFactory;

	/** @var ProfileImageProviderFactory */
	private $profileImageProviderFactory;

	/** @var TitleFactory */
	private $titleFactory;

	/**
	 * @param UrlUtils $urlUtils
	 * @param UserFactory $userFactory
	 * @param ProfileImageProviderFactory $profileImageProviderFactory
	 * @param TitleFactory $titleFactory
	 */
	public function __construct(
		UrlUtils $urlUtils,
		UserFactory $userFactory,
		ProfileImageProviderFactory $profileImageProviderFactory,
		TitleFactory $titleFactory
	) {
		$this->urlUtils = $urlUtils;
		$this->userFactory = $userFactory;
		$this->profileImageProviderFactory = $profileImageProviderFactory;
		$this->titleFactory = $titleFactory;
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
			$this->urlUtils, $this->userFactory, $this->profileImageProviderFactory
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
