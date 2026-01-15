<?php

namespace MediaWiki\Extension\VueJsPlus\Module;

use InvalidArgumentException;
use MediaWiki\Html\Html;
use MediaWiki\Html\HtmlJsCode;
use MediaWiki\MainConfigNames;
use MediaWiki\ResourceLoader\Context;
use MediaWiki\ResourceLoader\FileModule;
use MediaWiki\ResourceLoader\FilePath;
use RuntimeException;

class VueJsPlusModule extends FileModule {

	/** @var array */
	private $components = [];

	/** @var array */
	private static $filesCache = [];

	private bool $setupComplete = false;

	/**
	 * @inheritDoc
	 */
	public function __construct(
		array $options = [],
		$localBasePath = null,
		$remoteBasePath = null
	) {
		if ( isset( $options['vueJsPlusComponents'] ) ) {
			foreach ( $options['vueJsPlusComponents'] as $components ) {
				$this->components[] = $components;
			}
		}

		$options[ 'dependencies' ] = (array)( $options[ 'dependencies' ] ?? [] );
		if ( in_array( 'ext.vuejsplus', $options[ 'dependencies' ] ) ) {
			throw new InvalidArgumentException(
				'ResourceLoader modules using the VueJSPlusModule class cannot ' .
				"list the 'ext.vuejsplus' module as a dependency. " .
				"Instead, use 'vueJsPlusComponents' to require a subset of components."
			);
		}
		// Since we are using a lot of Codex components load codex if its not added from module
		if ( !in_array( '@wikimedia/codex', $options[ 'dependencies' ] ) ) {
			$options[ 'dependencies' ][] = '@wikimedia/codex';
		}
		parent::__construct( $options, $localBasePath, $remoteBasePath );
	}

	/**
	 * @inheritDoc
	 */
	public function getPackageFiles( Context $context ) {
		$this->setup( $context );
		$files = parent::getPackageFiles( $context );
		return $files;
	}

	/**
	 * @inheritDoc
	 */
	public function getDefinitionSummary( Context $context ) {
		$this->setup( $context );
		$test = parent::getDefinitionSummary( $context );
		return $test;
	}

	/**
	 * @inheritDoc
	 */
	public function getType(): string {
		return self::LOAD_GENERAL;
	}

	/**
	 * @inheritDoc
	 */
	public function getMessages() {
		$messages = parent::getMessages();

		// Add messages used inside Codex Vue components. The message keys are defined in the
		// "messageKeys.json" file
		if ( count( $this->components ) > 0 ) {
			$messageKeyFilePath = $this->makeFilePath( '/VueJsPlus/resources/messageKeys.json' )->getLocalPath();
			$messageKeys = json_decode( file_get_contents( $messageKeyFilePath ), true );
			$messages = array_merge( $messages, $messageKeys );
		}

		return $messages;
	}

	/**
	 * @inheritDoc
	 */
	public function supportsURLLoading() {
		return false;
	}

	/**
	 *
	 * @param Context $context
	 */
	private function setup( $context ) {
		if ( $this->setupComplete || defined( 'MW_QUIBBLE_CI' ) ) {
			return;
		}
		$files = $this->getVueJSPlusFiles( $context );

		$requestedFiles = array_map( static function ( $component ) use ( $files ) {
			if ( !isset( $files[ 'components' ][ $component ] ) ) {
				throw new InvalidArgumentException(
					"\"$component\" is not an export of VueJSPlus and" .
					"cannot be included in the \"vueJsPlusComponents\" array."
				);
			}
			return $files[ 'components' ][ $component ];
		}, $this->components );

		[ 'scripts' => $scripts, 'styles' => $styles ] = $this->resolveDependencies( $requestedFiles, $files );

		$exports = [];
		foreach ( $this->components as $component ) {
			$componentFile = $files[ 'components' ][ $component ];
			$exports[ $component ] = new HtmlJsCode(
				'require( ' . Html::encodeJsVar( "./_vuejsplus/$componentFile" ) . ' )'
			);
		}

		$syntheticExports = Html::encodeJsVar( HtmlJsCode::encodeObject( $exports ) );

		// Proxy the synthetic exports object so that we can throw a useful error if a component
		// is not defined in the module definition
		$proxiedSyntheticExports = <<<JAVASCRIPT
		module.exports = new Proxy( $syntheticExports, {
			get( target, prop ) {
				if ( !( prop in target ) ) {
					throw new Error(
						`VueJSPlus component "\${prop}" ` +
						'is not listed in the "vueJsPlusComponents" array ' +
						'of the "{$this->getName()}" module in your module definition file'
					);
				}
				return target[ prop ];
			}
		} );
		JAVASCRIPT;

		$this->packageFiles[] = [
			'name' => 'vuejsplus.js',
			'content' => $proxiedSyntheticExports
		];
		// Add each of the referenced scripts to the package
		foreach ( $scripts as $fileName ) {
			$this->packageFiles[] = [
				'name' => "_vuejsplus/$fileName",
				'file' => $this->makeFilePath( "/VueJsPlus/resources/vuejs/components/$fileName" )
			];
		}

		$this->setupComplete = true;
	}

	/**
	 *
	 * @param array $requestedFiles
	 * @param array $files
	 * @return void
	 */
	private function resolveDependencies( array $requestedFiles, array $files ) {
		$scripts = [];
		$styles = [];
		$gatherDependencies = static function ( $file ) use ( &$scripts, &$styles, $files, &$gatherDependencies ) {
			if ( !$file ) {
				return;
			}
			foreach ( $files[ 'files' ][ $file ][ 'dependencies' ] as $dep ) {
				if ( !in_array( $dep, $scripts ) ) {
					$gatherDependencies( $dep );
				}
			}
			$scripts[] = $file;
			$styles = array_merge( $styles, [] );
		};

		foreach ( $requestedFiles as $requestedFile ) {
			$gatherDependencies( $requestedFile );
		}

		return [ 'scripts' => $scripts, 'styles' => $styles ];
	}

	/**
	 *
	 * @param string $file
	 * @return FilePath
	 */
	private function makeFilePath( string $file ): FilePath {
		$remoteBasePath = $this->getConfig()->get( MainConfigNames::ExtensionDirectory );

		$filePath = new FilePath(
			'extensions' . $file,
			MW_INSTALL_PATH,
			$remoteBasePath
		);
		if ( !file_exists( $filePath->getLocalPath() ) ) {
			throw new RuntimeException( "Could not find VueJsComponent file `{$filePath->getLocalPath()}`" );
		}
		return $filePath;
	}

	/**
	 *
	 * @param Context $context
	 * @return array
	 */
	private function getVueJSPlusFiles( Context $context ): array {
		$remoteBasePath = $this->getConfig()->get( MainConfigNames::ExtensionDirectory );
		$manifestDir = '/VueJsPlus/resources/manifest.json';
		$manifestPath = $remoteBasePath . $manifestDir;
		$manifest = json_decode( file_get_contents( $manifestPath ), true );

		if ( isset( self::$filesCache[ $manifestPath ] ) ) {
			return self::$filesCache[ $manifestPath ];
		}

		$manifest = json_decode( file_get_contents( $manifestPath ), true );
		$files = [];
		$components = [];
		foreach ( $manifest as $key => $val ) {
			$files[ $val[ 'file' ] ] = [
				'styles' => $val[ 'css' ] ?? [],
				'dependencies' => array_map( static function ( $manifestKey ) use ( $manifest ) {
					// @phan-suppress-next-line PhanTypeArraySuspiciousNullable
					return $manifest[ $manifestKey ][ 'file' ];
				}, $val[ 'imports' ] ?? [] )
			];

			$isComponent = isset( $val[ 'isComponent' ] ) && $val[ 'isComponent' ];
			if ( $isComponent ) {
				$fileInfo = pathinfo( $val[ 'file' ] );
				$components[ $fileInfo[ 'filename' ] ] = $val[ 'file' ];
			}
		}

		self::$filesCache[ $manifestPath ] = [ 'files' => $files, 'components' => $components ];
		return self::$filesCache[ $manifestPath ];
	}
}
