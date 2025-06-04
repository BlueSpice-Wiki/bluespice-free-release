<?php

namespace BlueSpice\Avatars\Integration\PDFCreator\Utility;

use DOMDocument;
use DOMXPath;
use MediaWiki\Extension\PDFCreator\Utility\WikiFileResource;
use MediaWiki\Extension\UserProfile\ProfileImage\ProfileImageProviderFactory;
use MediaWiki\User\UserFactory;
use MediaWiki\Utils\UrlUtils;

class ImageFinder {

	/** @var UrlUtils */
	private $urlUtils;

	/** @var UserFactory */
	private $userFactory;

	/** @var ProfileImageProviderFactory */
	private $profileImageProviderFactory;

	/** @var array */
	protected $data = [];

	/**
	 * @param UrlUtils $urlUtils
	 * @param UserFactory $userFactory
	 * @param ProfileImageProviderFactory $profileImageProviderFactory
	 */
	public function __construct(
		UrlUtils $urlUtils,
		UserFactory $userFactory,
		ProfileImageProviderFactory $profileImageProviderFactory
	) {
		$this->urlUtils = $urlUtils;
		$this->userFactory = $userFactory;
		$this->profileImageProviderFactory = $profileImageProviderFactory;
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
			if ( !str_ends_with( $parseUrl['path'], '/dynamic-file-dispatcher/userprofileimage' ) ) {
				continue;
			}

			$username = $params['username'];
			$user = $this->userFactory->newFromName( $username );
			if ( !$user ) {
				continue;
			}
			$imageInfo = null;
			foreach ( $this->profileImageProviderFactory->getAll() as $handler ) {
				$imageInfo = $handler->provide( $user, $params );
				if ( $imageInfo ) {
					break;
				}
			}
			if ( !$imageInfo ) {
				continue;
			}

			$filename = basename( $imageInfo->getPath() );
			$absPath = $imageInfo->getPath();
			$matches = [];
			preg_match( '#(.*?)(bluespice/Avatars/)(/*thumb/)(.*?)(/.*)#', $absPath, $matches );
			if ( !empty( $matches ) ) {
				// thumb path
				unset( $matches[5] );
				unset( $matches[3] );
				unset( $matches[0] );
				$filename = $matches[4];
				$absPath = implode( '', $matches );
			}

			$filename = $this->uncollideFilenames( $filename, $absPath );

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

			$width = $params['width'] ? (int)$params['width'] : 32;
			$height = $params['height'] ? (int)$params['height'] : 32;
			$image->setAttribute( 'src', 'images/' . $filename );
			$image->setAttribute( 'width', ( $width / 60 ) . 'cm' );
			$image->setAttribute( 'height', ( $height / 60 ) . 'cm' );
		}
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
