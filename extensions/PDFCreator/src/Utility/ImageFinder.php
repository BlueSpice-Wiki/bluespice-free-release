<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use DOMDocument;
use DOMElement;
use DOMXPath;
use MediaWiki\Config\Config;
use MediaWiki\Title\TitleFactory;
use RepoGroup;

class ImageFinder {

	/** @var TitleFactory */
	protected $titleFactory;

	/** @var Config */
	protected $config;

	/** @var RepoGroup */
	protected $repoGroup;

	/** @var array */
	protected $data = [];

	/** @var array */
	protected $filenames = [];

	/**
	 * @param TitleFactory $titleFactory
	 * @param Config $config
	 * @param RepoGroup $repoGroup
	 */
	public function __construct(
		TitleFactory $titleFactory, Config $config, RepoGroup $repoGroup
	) {
		$this->titleFactory = $titleFactory;
		$this->config = $config;
		$this->repoGroup = $repoGroup;
	}

	/**
	 * @param array $pages
	 * @param array $resources
	 * @return array
	 */
	public function execute( array $pages, array $resources = [] ): array {
		$files = [];

		foreach ( $resources as $filename => $resourcePath ) {
			$this->data[$filename] = [
				'src' => [],
				'absPath' => $resourcePath,
				'filename' => $filename
			];
		}

		foreach ( $pages as $page ) {
			$dom = $page->getDOMDocument();
			$this->find( $dom );
		}

		foreach ( $this->data as $data ) {
			$files[] = new WikiFileResource(
				$data['src'],
				$data['absPath'],
				$data['filename']
			);
		}

		return $files;
	}

	/**
	 * @param DOMDocument $dom
	 * @return void
	 */
	protected function find( DOMDocument $dom ): void {
		$xpath = new DOMXPath( $dom );
		$images = $xpath->query(
			'//img',
			$dom
		);

		/** @var FileResolver */
		$fileResolver = $this->getFileResolver();

		/** @var DOMElement */
		foreach ( $images as $image ) {
			if ( !$image->hasAttribute( 'src' ) ) {
				continue;
			}

			$file = $fileResolver->execute( $image );
			if ( !$file ) {
				continue;
			}

			$absPath = $file->getLocalRefPath();
			$filename = $file->getName();
			$filename = $this->uncollideFilenames( $filename, $absPath );
			$url = $image->getAttribute( 'src' );

			if ( !isset( $this->data[$filename] ) ) {
				$this->data[$filename] = [
					'src' => [ $url ],
					'absPath' => $absPath,
					'filename' => str_replace( ':', '_', $filename )
				];
			} elseif ( $this->data[$filename]['absPath'] === $absPath ) {
				$urls = &$this->data[$filename]['src'];
				if ( !in_array( $url, $urls ) ) {
					$urls[] = $url;
				}
			}
		}
	}

	/**
	 * @return void
	 */
	protected function getFileResolver() {
		return new FileResolver(
			$this->config, $this->repoGroup, $this->titleFactory
		);
	}

	/**
	 * @param string $filename
	 * @param array $absPath
	 * @return string
	 */
	protected function uncollideFilenames( string $filename, string $absPath ): string {
		if ( !isset( $this->data[$filename] ) ) {
			return $filename;
		}

		if ( $this->data[$filename]['absPath'] === $absPath ) {
			return $filename;
		}

		$extPos = strrpos( $filename, '.' );
		$ext = substr( $filename, $extPos + 1 );
		$name = substr( $filename, 0, $extPos );

		$uncollide = 1;
		$newFilename = $filename;

		// TODO: Think about security bail out
		while ( isset( $this->data[$newFilename] ) && $this->data[$newFilename]['absPath'] !== $absPath ) {
			$uncollideStr = (string)$uncollide;
			$newFilename = "{$name}_{$uncollideStr}.{$ext}";
			$uncollide++;
		}
		return $newFilename;
	}
}
