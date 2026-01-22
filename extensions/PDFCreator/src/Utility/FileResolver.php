<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMElement;
use File;
use MediaWiki\Config\Config;
use MediaWiki\Title\TitleFactory;
use RepoGroup;

/*
 * @stable to extend
 */
class FileResolver {

	/** @var Config */
	protected $config;

	/** @var RepoGroup */
	protected $repoGroup;

	/** @var TitleFactory */
	protected $titleFactory;

	/**
	 * @param Config $config
	 * @param RepoGroup $repoGroup
	 * @param TitleFactory $titleFactory
	 */
	public function __construct(
		Config $config, RepoGroup $repoGroup, TitleFactory $titleFactory
	) {
		$this->config = $config;
		$this->repoGroup = $repoGroup;
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @param DOMElement $element
	 * @param string $attrSrc
	 * @return File|null
	 */
	public function execute( DOMElement $element, string $attrSrc = 'src' ): ?File {
		$pathsForRegex = [
			$this->config->get( 'Server' ),
			$this->config->get( 'ThumbnailScriptPath' ) . "?f=",
			$this->config->get( 'UploadPath' ),
			$this->config->get( 'ScriptPath' )
		];

		$src = $element->getAttribute( $attrSrc );
		if ( strpos( $src, '?' ) ) {
			$src = substr( $src, 0, strpos( $src, '?' ) );
		}

		// Extracting the filename
		foreach ( $pathsForRegex as $path ) {
			$src = preg_replace( "#" . preg_quote( $path, "#" ) . "#", '', $src );
			$src = preg_replace( '/(&.*)/', '', $src );
		}

		$srcUrl = urldecode( $src );
		$srcFilename = wfBaseName( $srcUrl );

		$thumbFilenameExtractor = new ThumbFilenameExtractor();
		$isThumb = $thumbFilenameExtractor->isThumb( $srcUrl );
		if ( $isThumb ) {
			// HINT: Thumbname-to-filename-conversion taken from includes/Upload/UploadBase.php
			// Check for filenames like 50px- or 180px-, these are mostly thumbnails
			$srcFilename = $thumbFilenameExtractor->extractFilename( $srcUrl );
		}
		$fileTitle = $this->titleFactory->newFromText( $srcFilename, NS_FILE );
		$file = $this->repoGroup->findFile( $fileTitle );

		// If not found, maybe its an archived file
		if ( !$file ) {
			$file = $this->findArchivedFile( $srcFilename );
		}

		if ( !$file || !$file->exists() ) {
			$file = null;
		}

		return $file;
	}

	/**
	 * @param string $filename
	 *
	 * @return File|null
	 */
	protected function findArchivedFile( string $filename ): ?File {
		$timestampPattern = '/^\d{14}!/';

		if ( !preg_match( $timestampPattern, $filename ) ) {
			return null;
		}

		$localGroup = $this->repoGroup->getLocalRepo();

		// Remove 14-digit timestamp and exclamation mark at the start
		$origFileName = preg_replace( $timestampPattern, '', $filename );

		$fileTitle = $this->titleFactory->makeTitle( NS_FILE, $origFileName );

		return $localGroup->newFromArchiveName( $fileTitle, $filename );
	}
}
