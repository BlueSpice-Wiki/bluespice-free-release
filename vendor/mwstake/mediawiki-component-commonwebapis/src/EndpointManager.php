<?php

namespace MWStake\MediaWiki\Component\CommonWebAPIs;

class EndpointManager {
	/** @var array|null */
	private $available = null;
	/** @var string */
	private $routeFilesDir;

	/**
	 * @param string $routeFilesDir
	 */
	public function __construct( string $routeFilesDir ) {
		$this->routeFilesDir = $routeFilesDir;
	}

	/**
	 * @return array
	 */
	public function getAvailableEndpoints(): array {
		$this->assertLoaded();
		return $this->available;
	}

	/**
	 * Add all route files to $wgRestAPIAdditionalRouteFiles
	 *
	 * @return void
	 */
	public function enableEndpoints() {
		foreach ( $this->getRoutes() as $file => $path ) {
			if ( !is_readable( $path ) ) {
				continue;
			}
			$relative = wfRelativePath( $path, $GLOBALS['IP'] );
			$GLOBALS['wgRestAPIAdditionalRouteFiles'][] = $relative;
		}
	}

	private function assertLoaded() {
		if ( $this->available === null ) {
			$this->available = [];
			foreach ( $this->getRoutes() as $file => $path ) {
				// Strip extension from $file
				$endpointKey = substr( $file, 0, strrpos( $file, '.' ) );
				$endpoint = json_decode( file_get_contents( $path ), 1 );
				if ( !is_array( $endpoint ) ) {
					continue;
				}
				$this->available[$endpointKey] = $endpoint;
			}
		}
	}

	/**
	 * @return array
	 */
	private function getRoutes(): array {
		$routes = [];
		$files = scandir( $this->routeFilesDir );
		foreach ( $files as $file ) {
			if ( $file === '.' || $file === '..' ) {
				continue;
			}
			$routes[$file] = $this->routeFilesDir . '/' . $file;
		}
		return $routes;
	}
}
