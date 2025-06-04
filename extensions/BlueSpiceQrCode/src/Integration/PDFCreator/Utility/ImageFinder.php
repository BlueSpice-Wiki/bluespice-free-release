<?php

namespace BlueSpice\QrCode\Integration\PDFCreator\Utility;

use BlueSpice\QrCode\DynamicFileDispatcher\QrCodeImage;
use DOMDocument;
use DOMXPath;
use File;
use FileRepo;
use MediaWiki\Extension\PDFCreator\Utility\WikiFileResource;
use MediaWiki\Title\TitleFactory;
use MediaWiki\Utils\UrlUtils;
use RepoGroup;

class ImageFinder {

	/** @var UrlUtils */
	private $urlUtils;

	/** @var TitleFactory */
	private $titleFactory;

	/** @var RepoGroup */
	private $repoGroup;

	/** @var array */
	protected $data = [];

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

		/** @var DOMElement */
		foreach ( $images as $image ) {
			if ( !$image->hasAttribute( 'src' ) ) {
				continue;
			}

			$src = $image->getAttribute( 'src' );

			$origUrl = $this->urlUtils->expand( $src );
			$parseUrl = $this->urlUtils->parse( $origUrl );
			$params = wfCgiToArray( $parseUrl['query'] );
			if ( !str_ends_with( $parseUrl['path'], '/dynamic-file-dispatcher/qrcode' ) ) {
				continue;
			}

			if ( !isset( $params['pagename'] ) ) {
				continue;
			}
			$pagename = $params['pagename'];

			if ( !isset( $params['query'] ) ) {
				continue;
			}
			$query = $params['query'];

			$title = $this->titleFactory->newFromText( $pagename );
			if ( !$title ) {
				continue;
			}

			$repo = $this->repoGroup->getRepoByName( 'QrCode' );
			if ( !$repo instanceof FileRepo ) {
				continue;
			}

			$qrCodeImage = new QrCodeImage(
				$this->titleFactory,
				$title->getPrefixedText(),
				$query ?? '',
				isset( $params['size'] ) ? (int)$params['size'] : 100
			);
			[ $status, $file ] = $qrCodeImage->generate();
			if ( !$file instanceof File ) {
				continue;
			}
			$absPath = $file->getLocalRefPath();
			$filename = $this->uncollideFilenames( $file->getName(), $absPath );

			if ( !isset( $this->data[$filename] ) ) {
				$this->data[$filename] = [
					'src' => [ $src ],
					'absPath' => $absPath,
					'filename' => str_replace( ':', '_', $filename )
				];
			} elseif ( $this->data[$filename]['absPath'] === $absPath ) {
				$urls = &$this->data[$filename]['src'];
				if ( !in_array( $src, $urls ) ) {
					$urls[] = $src;
				}
			}

			$width = $params['width'] ?? 100;
			$height = $params['height'] ?? 100;
			$size = max( $width, $height );
			$image->setAttribute( 'src', 'images/' . $filename );
			$image->setAttribute( 'width', ( $size / 60 ) . 'cm' );
			$image->setAttribute( 'height', ( $size / 60 ) . 'cm' );
		}
	}

	/**
	 * @param string $filename
	 * @param string $absPath
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
