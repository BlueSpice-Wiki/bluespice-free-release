<?php

namespace MediaWiki\Extension\PDFCreator\Processor;

use DOMElement;
use DOMXPath;
use MediaWiki\Config\Config;
use MediaWiki\Extension\PDFCreator\IProcessor;
use MediaWiki\Extension\PDFCreator\PDFCreator;
use MediaWiki\Extension\PDFCreator\Utility\ExportContext;
use MediaWiki\Extension\PDFCreator\Utility\ExportPage;
use MediaWiki\Extension\PDFCreator\Utility\UncollideFilename;
use MediaWiki\Title\TitleFactory;
use RepoGroup;

/**
 * Add a background image to pdfcreator-type-intro
 */
class Coverbackground implements IProcessor {

	/** @var Config */
	private $config;

	/** @var TitleFactory */
	private $titleFactory;

	/** @var RepoGroup */
	private $repoGroup;

	/**
	 * @param Config $config
	 * @param RepoGroup $repoGroup
	 * @param TitleFactory $titleFactory
	 */
	public function __construct( Config $config, RepoGroup $repoGroup, TitleFactory $titleFactory ) {
		$this->config = $config;
		$this->repoGroup = $repoGroup;
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function execute(
		array &$pages, array &$images, array &$attachments,
		?ExportContext $context = null, string $module = '', $params = []
	): void {
		if ( empty( $pages ) ) {
			return;
		}
		// Intro page is first page in pages array if a intro page is used
		/** @var ExportPage */
		$page = $pages[0];
		if ( $page->getType() !== PDFCreator::INTRO ) {
			return;
		}

		$coverbackground = $this->config->get( 'PDFCreatorCoverbackground' );
		if ( isset( $params['coverBackground'] ) && $params['coverBackground'] !== '' ) {
			$coverbackground = $params['coverBackground'];
		}
		if ( empty( $coverbackground ) ) {
			return;
		}

		$filename = '';
		$absFileSystemPath = '';

		$fileTitle = $this->titleFactory->makeTitle( NS_FILE, $coverbackground );
		if ( $fileTitle->exists() ) {
			$file = $this->repoGroup->findFile( $fileTitle );
			if ( $file && $file->getLocalRefPath() ) {
				$filename = $file->getName();
				$absFileSystemPath = $file->getLocalRefPath();
			}
		} else {
			// Variable does not contain file page
			if ( file_exists( $coverbackground ) ) {
				$last = strrpos( $coverbackground, DIRECTORY_SEPARATOR );
				$filename = substr( $coverbackground, $last + 1 );
				$absFileSystemPath = $coverbackground;
			}
		}

		if ( $filename === '' || $absFileSystemPath === '' ) {
			return;
		}

		$uncollideFilename = new UncollideFilename();
		$filename = $uncollideFilename->execute( $filename, $absFileSystemPath, $images );
		if ( !isset( $images[$filename] ) ) {
			$images[$filename] = $absFileSystemPath;
		}

		// Add background url in dom
		$dom = $page->getDOMDocument();
		$xpath = new DOMXPath( $dom );
		$introElements = $xpath->query(
			'//div[contains(@class, "pdfcreator-type-intro")]',
			$dom
		);
		if ( !$introElements || $introElements->count() === 0 ) {
			return;
		}

		$introElement = $introElements->item( 0 );
		if ( $introElement instanceof DOMElement === false ) {
			return;
		}

		$matches = [];
		$hasMatches = false;
		$style = '';
		if ( $introElement->hasAttribute( 'style' ) ) {
			$style = $introElement->getAttribute( 'style' );
			$hasMatches = preg_match( '/background-image:\s*url\((.*?)\)/', $style, $matches );
		}

		if ( !$hasMatches ) {
			$style .= ' background-image: url(\'images/' . $filename . '\');';
		} else {
			$newStyle = preg_replace(
				'/background-image:\s*url\((.*?)\)/',
				'background-image: url("images/' . $filename . '");',
				$style
			);
			if ( is_string( $newStyle ) ) {
				$style = $newStyle;
			}
		}

		$introElement->setAttribute( 'style', trim( $style ) );
	}

	/**
	 * @inheritDoc
	 */
	public function getPosition(): int {
		return 10;
	}

}
