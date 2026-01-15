<?php

namespace BlueSpice\Installer;

class BsEdition {

	/**
	 * BlueSpice edition file
	 *
	 * @var string
	 */
	private $editionFileName = 'BLUESPICE-EDITION';

	/**
	 * Selected BlueSpice edition
	 *
	 * @var string
	 */
	private $edition = 'pro';

	/**
	 * BlueSpice editions
	 *
	 * @var array
	 */
	private $editions = [ 'free', 'pro', 'cloud' ];

	/**
	 * Path to edition file
	 *
	 * @var string
	 */
	private $path = '';

	/**
	 *
	 * @param string $path
	 */
	public function __construct( string $path = '' ) {
		$this->path = $path;

		if ( $path === '' ) {
			$installPath = $GLOBALS['IP'];
			$this->path = $installPath . '/' . $this->editionFileName;
		}

		$this->fetchEdition();
	}

	/**
	 * BlueSpice edition for mainpage and sidebar
	 *
	 * File: $IP/BLUESPICE-EDITION
	 *
	 * @return void
	 */
	private function fetchEdition() {
		if ( file_exists( $this->path ) ) {
			$fileContent = file_get_contents( $this->path );
			$lines = explode( "\n", $fileContent );

			$edition = strtolower( trim( $lines[0] ) );
			if ( in_array( $edition, $this->editions ) ) {
				$this->edition = $edition;
			}
		}
	}

	/**
	 *
	 * @return string
	 */
	public function getEdition() {
		return $this->edition;
	}
}
