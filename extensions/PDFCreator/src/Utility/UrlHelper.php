<?php

namespace MediaWiki\Extension\PDFCreator\Utility;

use MediaWiki\Config\Config;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;

class UrlHelper {

	/** @var Config */
	private $config;

	/** @var TitleFactory */
	private $titleFactory;

	/**
	 * @param Config $config
	 * @param TitleFactory $titleFactory
	 */
	public function __construct( Config $config, TitleFactory $titleFactory ) {
		$this->config = $config;
		$this->titleFactory = $titleFactory;
	}

	/**
	 * @param string $url
	 * @return Title|null
	 */
	public function getTitleFromUrl( string $url ): ?Title {
		$parts = parse_url( $url );

		if ( !isset( $parts['path'] ) ) {
			return null;
		}

		$articlePath = $this->config->get( 'ArticlePath' );
		$articlePath = str_replace( '$1', '', $articlePath );
		$articlePath = rtrim( $articlePath, '/' ) . '/';
		$scriptPath = $this->config->get( 'ScriptPath' ) . '/';

		// Matches with nsfr_img_auth.php
		if (
			str_starts_with( $parts['path'], $scriptPath . 'img_auth.php' ) ||
			str_starts_with( $parts['path'], $articlePath . 'img_auth.php' ) ||
			str_starts_with( $parts['path'], $scriptPath . 'nsfr_img_auth.php' ) ||
			str_starts_with( $parts['path'], $articlePath . 'nsfr_img_auth.php' )
		) {
			$exploded = explode( '/', $parts['path'] );

			return $this->evaluateTitleText( end( $exploded ), true );
		}

		// Matches with index.php?title=
		if (
			( str_starts_with( $parts['path'], $scriptPath . 'index.php' ) ||
				str_starts_with( $parts['path'], $articlePath . 'index.php' ) ) &&
			isset( $parts['query'] ) &&
			str_contains( $parts['query'], 'title=' )
		) {

			return $this->evaluateTitleText( str_replace( "title=", '', $parts['query'] ) );
		}

		if ( str_starts_with( $parts['path'], $articlePath ) ) {
			return $this->evaluateTitleText( str_replace( $articlePath, '', $parts['path'] ) );
		}

		if ( str_starts_with( $parts['path'], $scriptPath ) ) {
			return $this->evaluateTitleText( str_replace( $scriptPath, '', $parts['path'] ) );
		}

		return null;
	}

	/**
	 * @param string $titleText
	 * @param bool $isFile
	 *
	 * @return Title|null
	 */
	private function evaluateTitleText( string $titleText, bool $isFile = false ): ?Title {
		if ( $isFile ) {
			$title = $this->titleFactory->newFromText( $titleText, NS_FILE );
		} else {
			$title = $this->titleFactory->newFromDBkey( $titleText );
		}

		if ( !$title ) {
			return null;
		}

		if ( !$title->canExist() ) {
			return null;
		}

		return $title;
	}
}
