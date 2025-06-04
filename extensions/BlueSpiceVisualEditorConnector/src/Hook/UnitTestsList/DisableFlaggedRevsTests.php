<?php
/*
 * This removes FlaggedRevs from the list of unit tests to be executed. FlaggedRevs is included by
 * VisualEditor, which is a dependency of VisualEditorConnector. While the tests in FlaggedRevs
 * repository pass for master and REL1_39, they fail, when the tests are executed within the context
 * of VisualEditorConnector. The most likely reason for this is, that FlaggedRevs uses a different
 * test template in gerrit/Jenkins. The fails are not related to VisualEditorConnector.
 *
 * Our best option for now is to remove the tests.
 */

namespace BlueSpice\VisualEditorConnector\Hook\UnitTestsList;

use BlueSpice\Hook;
use MediaWiki\Config\Config;
use MediaWiki\Context\IContextSource;

class DisableFlaggedRevsTests extends Hook {

	/**
	 *
	 * @var array
	 */
	protected $paths;

	/**
	 *
	 * @param array &$paths A list of paths to extension unit test folders
	 * @return mixed
	 */
	public static function callback( &$paths ) {
		$className = static::class;
		$hookHandler = new $className(
			null,
			null,
			$paths
		);
		return $hookHandler->process();
	}

	/**
	 *
	 * @param IContextSource $context
	 * @param Config $config
	 * @param array &$paths A list of paths to extension unit test folders
	 *
	 * @return void
	 */
	public function __construct( $context, $config, &$paths ) {
		parent::__construct( $context, $config );
		$this->paths = &$paths;
	}

	/**
	 * @return bool
	 */
	protected function doProcess() {
		$this->paths = array_filter( $this->paths, static function ( $path ) {
			return !str_contains( $path, 'FlaggedRevs/tests/phpunit' );
		} );

		return true;
	}
}
